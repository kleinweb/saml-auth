# SPDX-FileCopyrightText: 2024-2025 Temple University <kleinweb@temple.edu>
#
# SPDX-License-Identifier: GPL-3.0-or-later

(builtins.getFlake ("git+file://" + toString ./.)).devShells.${builtins.currentSystem}.default
