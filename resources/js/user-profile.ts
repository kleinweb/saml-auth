// SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

document.addEventListener('DOMContentLoaded', () => {
  // TODO: double check selector uniqueness
  const $formTable = document.querySelector('.form-table') as HTMLElement
  const $message = document.createElement('div')
  const $messageBody = document.createElement('p')

  $message.className = 'updated fade'
  $message.appendChild($messageBody)
  $messageBody.textContent =
    'Some profile fields cannot be changed from WordPress.'

  $formTable?.before($message)

  const $firstName = document.querySelector('#first_name') as HTMLInputElement
  const $lastName = document.querySelector('#last_name') as HTMLInputElement
  const $email = document.querySelector('#email') as HTMLInputElement
  const $emailDescription = document.querySelector(
    '#email-description',
  ) as HTMLElement

  $firstName.disabled = true
  $lastName.disabled = true
  $email.disabled = true
  $emailDescription.hidden = true
})
