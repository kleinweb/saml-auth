# SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later
{
  perSystem =
    {
      config,
      inputs',
      pkgs,
      ...
    }:
    let
      checksPkgs = [
        config.pre-commit.settings.hooks.markdownlint.package
        config.pre-commit.settings.hooks.yamllint.package
      ];

      buildsPkgs = [
        pkgs.turbo
      ];

      deployPkgs = [
        pkgs.rsync
      ];

      formatterPkgs = [
        pkgs.dos2unix
        pkgs.nixfmt
        pkgs.prettier
        pkgs.taplo
        pkgs.treefmt
      ];

      releasePkgs = [
        pkgs.cocogitto
      ];

      commonPkgs = [
        pkgs.biome
        pkgs.curl
        pkgs.fd
        pkgs.gnused
        pkgs.jq
        pkgs.moreutils
        pkgs.ripgrep
        pkgs.nodejs
        pkgs.php
        pkgs.php.packages.composer
        pkgs.xq-xml
      ];

      developmentPkgs =
        commonPkgs ++ checksPkgs ++ buildsPkgs ++ deployPkgs ++ formatterPkgs ++ releasePkgs;
    in
    {
      devShells.default = pkgs.mkShellNoCC {
        shellHook = ''
          : "''${PRJ_BIN_HOME:=''${PRJ_PATH:-''${PRJ_ROOT}/.bin}}"

          export PRJ_BIN_HOME

          ${config.pre-commit.installationScript}
        '';
        nativeBuildInputs = developmentPkgs ++ [
          # pre-commit helper tool to simplify file matching.  For example,
          # the `yml` and `yaml` extensions share the same "type" of `yaml`.
          # Otherwise, you would need to write a regexp for both extensions.
          # <https://pre-commit.com/#filtering-files-with-types>
          # NOTE: The command is `identify-cli`, not to be confused with
          # imagemagick's `identify`.
          pkgs.python311Packages.identify
        ];
      };

      devShells.ci = pkgs.mkShellNoCC {
        nativeBuildInputs = commonPkgs ++ checksPkgs ++ buildsPkgs ++ deployPkgs;
      };
    };
}
