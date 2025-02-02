{
  description = "TwirPHP: PHP port of Twitch's Twirp RPC framework";

  inputs = {
    nixpkgs.url = "nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs = { self, nixpkgs, flake-utils, ... }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = import nixpkgs {
          inherit system;

          overlays = [
            (final: prev: {
              clientcompat = prev.buildGoPackage rec {
                pname = "clientcompat";
                version = "8.1.3";

                goPackagePath = "github.com/twitchtv/twirp";

                src = pkgs.fetchFromGitHub {
                  owner = "twitchtv";
                  repo = "twirp";
                  rev = "v${version}";
                  sha256 = "sha256-p3gHVHGBHakOOQnJAuMK7vZumNXN15mOABuEHUG0wNs=";
                };

                subPackages = [ "clientcompat" ];
              };
            })
          ];
        };
      in
      rec
      {
        packages = {
          default = packages.protoc-gen-twirp_php;

          protoc-gen-twirp_php = pkgs.buildGoModule rec {
            pname = "protoc-gen-twirp_php";
            version = "0.9.1";

            src = ./.;

            vendorSha256 = "sha256-Kz9tMM4XSMOUmlHb/BE5/C/ZohdE505DTeDj9lGki/I=";

            subPackages = [ "protoc-gen-twirp_php" ];

            ldflags = [
              "-w"
              "-s"
              "-X main.version=v${version}"
            ];
          };
        };

        devShells = {
          default = pkgs.mkShell {
            buildInputs = with pkgs; [
              git
              gnumake

              (php.withExtensions ({ enabled, all }: enabled ++ [ all.xdebug ]))
              php.packages.composer

              go
              protobuf
              gotestsum
              clientcompat

              golangci-lint
              php.packages.phpstan
              php.packages.php-cs-fixer
              php.packages.psalm

              goreleaser
            ];

            shellHook = ''
              ${pkgs.go}/bin/go version
              ${pkgs.php}/bin/php -v
            '';
          };

          ci = devShells.default;
        };
      });
}
