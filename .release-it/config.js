const fs   = require('fs');
const path = require('path');

const mainTemplate   = fs.readFileSync(path.resolve(__dirname, './templates/template.hbs')).toString();
const commitTemplate = fs.readFileSync(path.resolve(__dirname, './templates/commit.hbs')).toString();
const types          = [
    {type: 'feat', section: 'Features'},
    {type: 'feature', section: 'Features'},
    {type: 'fix', section: 'Bug Fixes'},
    {type: 'perf', section: 'Performance Improvements'},
    {type: 'revert', section: 'Reverts'},
    {type: 'chore', section: 'Miscellaneous Chores'},
    {type: 'docs', section: 'Documentation', hidden: true},
    {type: 'refactor', section: 'Code Refactoring', hidden: true},
    {type: 'test', section: 'Tests', hidden: true},
    {type: 'ci', section: 'Continuous Integration', hidden: true},
];

module.exports = {
    npm:     false,
    git:     {
        tagArgs:        '-S',
        commitArgs:     '-S',
        requireCommits: true,
    },
    github:  {
        release: true,
        draft:   true,
    },
    plugins: {
        '@release-it/bumper':                 {
            out: {
                file: 'packages/*/metadata.json',
            },
        },
        '@release-it/conventional-changelog': {
            preset:            'conventionalcommits',
            gitRawCommitsOpts: {
                merges: null,
            },
            writerOpts:        {
                mainTemplate:  mainTemplate,
                commitPartial: commitTemplate,
                transform:     (commit, context) => {
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