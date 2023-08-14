const fs   = require('fs');
const path = require('path');

const mainTemplate       = fs.readFileSync(path.resolve(__dirname, './templates/template.hbs')).toString();
const commitTemplate     = fs.readFileSync(path.resolve(__dirname, './templates/commit.hbs')).toString();
const args               = require('minimist')(process.argv.slice(2), {
    string:  ['release.name', 'release.description'],
    default: {
        'release.name':        null,
        'release.description': null,
    },
});
const releaseName        = args.release.name || 'Release ${version}';
const releaseDescription = args.release.description;
const types              = [
    {type: 'feat', section: 'Features'},
    {type: 'feature', section: 'Features'},
    {type: 'fix', section: 'Bug Fixes'},
    {type: 'perf', section: 'Performance Improvements'},
    {type: 'revert', section: 'Reverts'},
    {type: 'chore', section: 'Miscellaneous Chores'},
    {type: 'deprecate', section: 'Deprecations'},
    {type: 'docs', section: 'Documentation', hidden: true},
    {type: 'refactor', section: 'Code Refactoring', hidden: true},
    {type: 'test', section: 'Tests', hidden: true},
    {type: 'ci', section: 'Continuous Integration', hidden: true},
];

if (!releaseName) {
    throw new Error('The release name is required! Please specify it with `--release.name="name"`.');
}

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
                    context.release = {
                        name:        releaseName.replaceAll('${version}', context.version),
                        description: releaseDescription,
                        breaking:    !!commits.some(commit => commit.breaking),
                    };

                    return context;
                },
                commitsSort:     (a, b) => {
                    // todo(release-it): Is it possible to sort by commit datetime?
                    return (a.scope || '').localeCompare(b.scope || '')
                        || a.subject.localeCompare(b.subject);
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

                    // Properties
                    commit.type  = type.section;
                    commit.scope = commit.scope === '*' ? null : commit.scope;

                    // Custom
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
