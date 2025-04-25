# SPDX-FileCopyrightText: 2024-2025 Temple University <kleinweb@temple.edu>
#
# SPDX-License-Identifier: GPL-3.0-or-later

{ lib, ... }:
{
  perSystem =
    { config, pkgs, ... }:
    {
      apps.php-lint-project = {
        type = "app";
        program = lib.getExe (
          pkgs.writeShellApplication {
            name = "php-lint-project";
            runtimeInputs = [
              pkgs.fd
              config.packages.php-lint
            ];
            text = ''
              fd --type file --extension php --hidden --exec-batch \
                php-lint --show-deprecated
            '';
          }
        );
      };
      packages.php-lint = pkgs.writeShellApplication {
        name = "php-lint";
        runtimeInputs = [ pkgs.php ];
        text = ''
          ${lib.getExe pkgs.php.packages.php-parallel-lint} \
            --exclude .git --exclude .cache --exclude .direnv \
            --exclude vendor --exclude node_modules \
            "$@"
        '';
      };

    };
}
