# SPDX-FileCopyrightText: 2024-2025 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later
{
  description = "Kleinweb SAML Auth";
  inputs = {
    beams.url = "github:kleinweb/beams";

    flake-parts.url = "github:hercules-ci/flake-parts";
    pre-commit-hooks.follows = "beams/pre-commit-hooks";

    nixos-unstable.url = "github:NixOS/nixpkgs/nixos-unstable";
    nixpkgs-trunk.url = "github:NixOS/nixpkgs/master";
    nixpkgs.follows = "nixos-unstable";
  };

  outputs =
    inputs@{ flake-parts, ... }:
    flake-parts.lib.mkFlake { inherit inputs; } {
      systems = [
        "x86_64-linux"
        "aarch64-darwin"
        "aarch64-linux"
      ];

      imports = [
        inputs.pre-commit-hooks.flakeModule

        ./nix/devshells.nix
        ./nix/git-hooks.nix
      ];

      perSystem =
        { inputs', system, ... }:
        {
          _module.args.pkgs = import inputs.nixpkgs {
            inherit system;
            overlays = [
              (_final: prev: {
                just = inputs'.nixpkgs-trunk.legacyPackages.just;
                nixfmt = prev.nixfmt-rfc-style;
                php = prev.php82;
                treefmt = prev.treefmt2;
              })
            ];
          };
        };
    };

  nixConfig = {
    extra-experimental-features = "nix-command flakes";
    extra-substituters = [
      "https://kleintu.cachix.org"
      "https://dotfield.cachix.org"
      "https://nix-community.cachix.org"
    ];
    extra-trusted-public-keys = [
      "kleintu.cachix.org-1:NMaPJijd2bOo4ECoHaAbkUBESvPFj4qj5FgPy+6Hg2s="
      "dotfield.cachix.org-1:b5H/ucY/9PDARWG9uWA87ZKWUBU+hnfF30amwiXiaNk="
      "nix-community.cachix.org-1:mB9FSh9qf2dCimDSUo8Zy7bkq5CX+/rkCWyvRCYg3Fs="
    ];
  };
}
