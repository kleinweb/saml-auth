/*!
 * SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

import {assign, createActor, createMachine, setup} from 'xstate'

enum Idp {
  LOCAL = 'local',
  SAML = 'saml',
}

const initialState = Idp.SAML

document.addEventListener('DOMContentLoaded', () => {
  const $toggleBtn = document.body.querySelector(
    '.js-kleinweb-auth-idp-toggle-button',
  )

  // https://github.com/WordPress/wordpress-develop/blob/b42f5f95417413ee6b05ef389e21b3a0d61d3370/src/wp-login.php#L1513
  const $passwordInput = document.body.querySelector('#user_pass')

  if (
    !($toggleBtn instanceof HTMLElement) ||
    !($passwordInput instanceof HTMLInputElement)
  ) {
    console.error('[kleinweb-auth]: Something is very wrong...', {
      $toggleBtn,
      $passwordInput,
    })
    return
  }

  const toggleMachine = setup({
    types: {
      context: {} as {
        buttonText: string
      },
    },
  }).createMachine({
    id: 'toggle',
    initial: initialState,
    context: () => ({
      buttonText: $toggleBtn.innerHTML.trim(),
    }),
    states: {
      [Idp.SAML]: {
        on: {
          TOGGLE: {
            target: Idp.LOCAL,
          },
        },
        exit: assign({
          buttonText: 'Log in with TU AccessNet',
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
            $passwordInput.disabled = false
          },
        ],

        exit: [
          () => {
            $passwordInput.disabled = true
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
