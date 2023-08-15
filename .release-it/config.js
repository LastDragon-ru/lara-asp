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
 * - The `mark` property (expects emoji) allow mark the type section, the mark
 *   will also be added into the Legend section of the changelog.
 */
const types        = [
    {
        description: 'A new feature.',
        section:     'Features',
        hidden:      false,
        type:        'feat',
        mark:        null,
    },
    {
        description: 'A code change that improves performance.',
        section:     'Performance Improvements',
        hidden:      false,
        type:        'perf',
        mark:        null,
    },
    {
        description: 'A bug fix.',
        section:     'Bug Fixes',
        hidden:      false,
        type:        'fix',
        mark:        null,
    },
    {
        description: 'A code change that deprecate a part of public API.',
        section:     'Deprecations',
        hidden:      false,
        type:        'deprecate',
        mark:        '💀',
    },
    {
        description: 'Something important but not related to any other categories.',
        section:     'Miscellaneous',
        hidden:      false,
        type:        'chore',
        mark:        null,
    },
    {
        description: 'A code change that reverts previous change.',
        section:     'Reverts',
        hidden:      false,
        type:        'revert',
        mark:        null,
    },
    {
        description: 'Documentation only changes',
        section:     'Documentation',
        hidden:      true,
        type:        'docs',
        mark:        null,
    },
    {
        description: 'An important code change that does not introduce new features or fix issues but restructuring existing code.',
        section:     'Code Refactoring',
        hidden:      true,
        type:        'refactor',
        mark:        null,
    },
    {
        description: 'Adding missing/new tests or correcting existing.',
        section:     'Tests',
        hidden:      true,
        type:        'test',
        mark:        null,
    },
    {
        description: 'Changes to CI configuration files and scripts.',
        section:     'Continuous Integration',
        hidden:      true,
        type:        'ci',
        mark:        null,
    },
];
const breakingMark = {
    name: 'Breaking changes',
    icon: '☣',
};

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
                    // Group commits by package and type, collect summary
                    let breaking   = {count: 0, packages: []};
                    let packages   = {};
                    let summary    = {};
                    const sections = types.reduce((result, type, index) => {
                        result[type.section] = {
                            priority: index,
                            mark:     type.mark,
                        };

                        return result;
                    }, {});

                    for (let commit of commits) {
                        // Comment may have multiple scopes (separated by `,`),
                        // each scope may have component (after `/`).
                        const pkgs   = [];
                        const scopes = (commit.scope || '')
                            .split(',')
                            .map(scope => scope.trim())
                            .filter((v, i, a) => a.indexOf(v) === i);

                        for (let scope of scopes) {
                            const parts     = scope.split('/');
                            const package   = parts[0].trim();
                            const component = parts.slice(1).join('/').trim() || null;
                            const byPackage = packages[package] = packages[package] || {
                                name:  package,
                                types: {},
                            };
                            const byType    = byPackage.types[commit.section] = byPackage.types[commit.section] || {
                                name:    commit.section,
                                mark:    sections[commit.section].mark,
                                commits: [],
                            };

                            byType.commits.push(Object.assign({}, commit, {
                                scope: component,
                            }));

                            pkgs.push(package);
                        }

                        // Summary
                        if (summary[commit.section]) {
                            summary[commit.section].count++;
                            summary[commit.section].packages.push(...pkgs);
                        } else {
                            summary[commit.section] = {
                                name:     commit.section,
                                icon:     sections[commit.section].mark,
                                count:    1,
                                packages: pkgs,
                            };
                        }

                        // Breaking change?
                        if (commit.breaking) {
                            breaking.count++;
                            breaking.packages.push(...pkgs);
                        }
                    }

                    // Sort by names/scope/subject
                    packages = Object.values(packages);
                    packages
                        .sort((a, b) => compareStrings(a.name, b.name))
                        .forEach((package) => {
                            package.types = Object.values(package.types);
                            package.types
                                .sort((a, b) => {
                                    return comparePriority(sections[a.name], sections[b.name], Number.MAX_SAFE_INTEGER)
                                        || compareStrings(a.name, b.name);
                                })
                                .forEach((type) => {
                                    type.commits.sort((a, b) => {
                                        return compareStrings(a.scope, b.scope)
                                            || compareStrings(a.subject, b.subject)
                                    })
                                });
                        });

                    // Summary
                    summary = Object.values(summary);

                    if (breaking.count) {
                        summary.unshift(Object.assign(breaking, breakingMark));
                    }

                    summary
                        .sort((a, b) => {
                            return comparePriority(sections[a.name], sections[b.name], Number.MIN_SAFE_INTEGER)
                                || compareStrings(a.name, b.name);
                        })
                        .forEach((type) => {
                            type.packages = type.packages
                                .filter((v, i, a) => v && a.indexOf(v) === i)
                                .sort(compareStrings);
                        });

                    // Update context
                    context.release  = {
                        name:        releaseName.replaceAll('${version}', context.version),
                        description: releaseDescription,
                        breaking:    breaking.count > 0,
                    };
                    context.packages = packages;
                    context.summary  = summary;

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
                    commit.marks    = breaking ? [breakingMark.icon] : [];
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

// Helpers
const compareStrings  = (a, b, trim = /^[*`_~]+/g) => {
    // The strings may contain the markdown, so we are
    // removing "invisible" chars before comparing.
    a = (a || '').trimStart().replace(trim, '');
    b = (b || '').trimStart().replace(trim, '');

    return a.localeCompare(b);
};
const comparePriority = (a, b, d) => {
    a = a && Number.isInteger(a.priority) ? a.priority : d;
    b = b && Number.isInteger(b.priority) ? b.priority : d;

    return a - b;
};
