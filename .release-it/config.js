const fs   = require('fs');
const path = require('path');

const mainTemplate       = fs.readFileSync(path.resolve(__dirname, './templates/template.hbs')).toString();
const commitTemplate     = fs.readFileSync(path.resolve(__dirname, './templates/commit.hbs')).toString();
const args               = require('minimist')(process.argv.slice(2), {
    string:  ['release.name', 'release.description', 'dump.changelog'],
    default: {
        'release.name':        null,
        'release.description': null,
        'dump.changelog':      null,
    },
});
const releaseName        = args.release.name || 'Release ${version}';
const releaseDescription = args.release.description;

if (!releaseName) {
    throw new Error('The release name is required! Please specify it with `--release.name="name"`.');
}

/**
 * Types
 *
 * - The changelog will contain types in the same order as they defined.
 * - The types with `hidden: true` are not included in the changelog unless they
 *   have a breaking change (`!`) mark.
 */
const types = [
    {
        description: 'A new feature.',
        section:     'Features',
        hidden:      false,
        type:        'feat',
    },
    {
        description: 'A code change that improves performance.',
        section:     'Performance Improvements',
        hidden:      false,
        type:        'perf',
    },
    {
        description: 'A bug fix.',
        section:     'Bug Fixes',
        hidden:      false,
        type:        'fix',
    },
    {
        description: 'A code change that deprecate a part of public API.',
        section:     'Deprecations',
        hidden:      false,
        type:        'deprecate',
    },
    {
        description: 'Something important but not related to any other categories.',
        section:     'Miscellaneous',
        hidden:      false,
        type:        'chore',
    },
    {
        description: 'A code change that reverts previous change.',
        section:     'Reverts',
        hidden:      false,
        type:        'revert',
    },
    {
        description: 'Documentation only changes',
        section:     'Documentation',
        hidden:      true,
        type:        'docs',
    },
    {
        description: 'An important code change that does not introduce new features or fix issues but restructuring existing code.',
        section:     'Code Refactoring',
        hidden:      true,
        type:        'refactor',
    },
    {
        description: 'Adding missing/new tests or correcting existing.',
        section:     'Tests',
        hidden:      true,
        type:        'test',
    },
    {
        description: 'Changes to CI configuration files and scripts.',
        section:     'Continuous Integration',
        hidden:      true,
        type:        'ci',
    },
];

module.exports = {
    npm:     false,
    git:     {
        tagArgs:        '-s',
        commitArgs:     '-S',
        requireCommits: true,
        commitMessage:  `release: ${releaseName}${releaseDescription ? '\n\n' + releaseDescription : ''}`,
    },
    github:  {
        release:      true,
        draft:        true,
        comments:     false,
        releaseName:  releaseName,
        releaseNotes: (context) => {
            // Dump
            // waiting for https://github.com/release-it/release-it/issues/1031
            if (args.dump.release) {
                fs.writeFileSync(args.dump.release, context.changelog);
            }

            // The GitHub release already includes a header, so there is no need
            // for a second one.
            let changelog = context.changelog;
            const lines   = changelog.split('\n');

            if (lines.length > 1) {
                const header = lines[0];
                const body   = lines.slice(1).join('\n');
                changelog    = `<!-- ${header} -->\n${body}`;
            }

            return changelog;
        },
    },
    plugins: {
        '@release-it/bumper':                 {
            out: {
                file: 'packages/*/metadata.json',
            },
        },
        '@release-it/conventional-changelog': {
            preset:            {
                name:  'conventionalcommits',
                types: types,
            },
            gitRawCommitsOpts: {
                merges: null,
            },
            writerOpts:        {
                mainTemplate:    mainTemplate,
                commitPartial:   commitTemplate,
                finalizeContext: (context, options, commits, keyCommit) => {
                    // Group commits by package and type
                    let packages = commits.reduce((result, commit) => {
                        // Comment may have multiple scopes (separated by `,`), each scope may have component (after `/`).
                        const scopes = (commit.scope || '')
                            .split(',')
                            .map(scope => scope.trim())
                            .filter((v, i, a) => a.indexOf(v) === i);

                        for (let scope of scopes) {
                            const parts     = scope.split('/');
                            const package   = parts[0].trim();
                            const component = parts.slice(1).join('/').trim() || null;
                            const byPackage = result[package] = result[package] || {
                                name:  package,
                                types: {},
                            };
                            const byType    = byPackage.types[commit.section] = byPackage.types[commit.section] || {
                                name:    commit.section,
                                commits: [],
                            };

                            byType.commits.push(Object.assign({}, commit, {
                                scope: component,
                            }));
                        }

                        return result;
                    }, {});

                    // Sort by names/scope/subject
                    packages             = Object.values(packages);
                    const trim           = /^[*`_~]+/g;
                    const compareStrings = (a, b) => {
                        // The strings may contain the markdown, so we are
                        // removing "invisible" chars before comparing.
                        a = (a || '').trimStart().replace(trim, '');
                        b = (b || '').trimStart().replace(trim, '');

                        return a.localeCompare(b);
                    };
                    const compareNumbers = (a, b) => {
                        a = Number.isInteger(a) ? a : Number.MAX_SAFE_INTEGER;
                        b = Number.isInteger(b) ? b : Number.MAX_SAFE_INTEGER;

                        return a - b;
                    };
                    const sections       = types.reduce((result, type, index) => {
                        result[type.section] = index;

                        return result;
                    }, {});

                    packages
                        .sort((a, b) => compareStrings(a.name, b.name))
                        .forEach((package) => {
                            package.types = Object.values(package.types);
                            package.types
                                .sort((a, b) => {
                                    return compareNumbers(sections[a.name], sections[b.name])
                                        || compareStrings(a.name, b.name);
                                })
                                .forEach((type) => {
                                    type.commits.sort((a, b) => {
                                        return compareStrings(a.scope, b.scope)
                                            || compareStrings(a.subject, b.subject)
                                    })
                                });
                        });

                    // Update context
                    context.release  = {
                        name:        releaseName.replaceAll('${version}', context.version),
                        description: releaseDescription,
                        breaking:    !!commits.some(commit => commit.breaking),
                    };
                    context.packages = packages;

                    // Return
                    return context;
                },
                transform:       (commit, context) => {
                    // Type?
                    const breaking = commit.notes.length > 0;
                    const type     = types.find(t => t.type === commit.type);

                    if (!type || (type.hidden && !breaking)) {
                        return null;
                    }

                    // Cleanup subject (github adds #issue on the end of PR message, we are no need it)
                    commit.subject = commit.subject.trim().replace(/\.+$/, '').trim();

                    for (let reference of commit.references) {
                        let patterns = [
                            `(${reference.prefix}${reference.issue})`,
                            `${reference.prefix}${reference.issue}`,
                        ];

                        for (let pattern of patterns) {
                            if (commit.subject.endsWith(pattern)) {
                                commit.subject = commit.subject.slice(0, -pattern.length).trim();
                            }
                        }
                    }

                    // Custom
                    commit.mentions = []; // see https://github.com/conventional-changelog/conventional-changelog/issues/601
                    commit.section  = type.section;
                    commit.breaking = breaking;
                    commit.related  = [...new Set([
                        ...commit.references.map((r) => `${r.prefix}${r.issue}`),
                        commit.hash,
                    ])].sort();

                    // Return
                    return commit;
                },
            },
        },
    },
};
