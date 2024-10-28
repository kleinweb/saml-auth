/*!
 * SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

import {assign, createActor, setup} from 'xstate'

enum Idp {
  LOCAL = 'local',
  SAML = 'saml',
}

const defaultToggleButtonText = 'Log in with TU AccessNet'

document.addEventListener('DOMContentLoaded', () => {
  const $password = document.querySelector('#user_pass') as HTMLInputElement
  const $toggleBtn = document.querySelector(
    '.js-kleinweb-auth-idp-toggle-button',
  ) as HTMLAnchorElement

  const toggleMachine = setup({
    types: {
      context: {} as {
        buttonText: string
      },
    },
  }).createMachine({
    id: 'toggle',
    initial: Idp.SAML,
    context: () => ({
      buttonText: $toggleBtn.textContent?.trim() ?? defaultToggleButtonText,
    }),
    states: {
      [Idp.SAML]: {
        on: {
          TOGGLE: {
            target: Idp.LOCAL,
          },
        },
        exit: assign({
          buttonText: defaultToggleButtonText,
        }),
      },
      [Idp.LOCAL]: {
        on: {
          TOGGLE: {
            target: Idp.SAML,
          },
        },

        entry: [
          () => {
            $password.disabled = false
          },
        ],

        exit: [
          () => {
            $password.disabled = true
          },
          assign({
            buttonText: 'Use local account',
          }),
        ],
      },
    },
  })

  const actor = createActor(toggleMachine)

  actor.subscribe(snapshot => {
    const {context: ctx} = snapshot

    document.body.dataset['kleinwebAuthIdp'] = snapshot.value
    $toggleBtn.textContent = ctx.buttonText
  })

  actor.start()

  $toggleBtn.addEventListener('click', e => {
    const prevSnapshot = actor.getSnapshot()

    e.preventDefault()

    actor.send({type: 'TOGGLE', prevContext: prevSnapshot.context})
  })
})
