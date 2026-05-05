# SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later

import 'common.vars.just'

wp-header-version-prefix-regexp :='(\s*)(\*\s)?Version:(\s+)'

[confirm: "Prepare and tag release?"]
[doc: "Prepare and tag a release based on semantic commits"]
[no-cd]
release *ARGS='--auto':
  cog bump {{ARGS}}

[doc: "Bump version numbers across manifest files"]
[no-cd]
bump old new:
  @just release::_bump-json {{new}} {{ join(prj-root, "composer.json") }}
  @for file in `rg '^{{wp-header-version-prefix-regexp}}([[:alnum:]\.-]+)$' \
    --files-with-matches {{prj-root}}`; do \
    just release::_bump-wp-header {{old}} {{new}} $file; \
  done

@_bump-json version file:
  jq --arg ver '{{version}}' '.version = $ver' {{file}} \
    | sponge {{file}}
  echo Bumped {{file}} version to \'{{version}}\'
  biome format --diagnostic-level=warn --write {{file}} >/dev/null

@_bump-wp-header old new file:
  sed -E -i 's/^({{wp-header-version-prefix-regexp}}){{old}}/\1{{new}}/' {{file}}
  echo Bumped {{file}} version to \'{{new}}\'
