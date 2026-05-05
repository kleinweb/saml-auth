# SPDX-FileCopyrightText: 2022-2026 Temple University <kleinweb@temple.edu>
# SPDX-License-Identifier: GPL-3.0-or-later

###: <https://just.systems/man/en/>

import ".config/common.vars.just"

mod qa ".config/qa.just"
mod release ".config/release.just"
mod reuse ".config/reuse.just"

alias check := qa::check
alias lint := qa::lint
alias fix := qa::fix
alias fmt := qa::fmt

default:
  @just --choose
