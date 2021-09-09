window.onload = function() {
    registerAddButton()
    registerDeleteButtons()
}

// We'll refer to this a couple of times, so I made it a global
const fields = [
    'name',
    'dept',
    'phone',
    'term',
]

/**
 * Sets up the event listener for our "Add Member" button.
 *
 * @return void
 */
function registerAddButton() {
    const addButton = document.querySelector('#addButton')
    addButton.addEventListener('click', addNewMember)
}

/**
 * Registers an event listener on the parent container of our member
 * divs, which deletes whichever such div a user click the "Delete"
 * button for.
 *
 * @return void
 */
function registerDeleteButtons() {
    const memberBox = document.querySelector('#members')
    memberBox.addEventListener('click', event => {
        if (event.target.getAttribute('type') == 'button' && event.target.classList.contains('delete')) {
            const indexPattern = /^delete-(\d+)$/
            const matches = event.target.id.match(indexPattern)
            if (matches.length) {
                const index = matches[1]
                deleteMember(`member-${index}`)
            }
        }
    })
}

/**
 * Deletes a given Member div. If there's only one, clears the inputs
 * instead.
 *
 * @param {String} memberId The id attribute of the div to delete.
 *
 * @return void
 */
function deleteMember(memberId) {
    const memberTotal = document.querySelectorAll('.member-box').length
    const memberBox = document.getElementById(memberId)

    if (memberTotal > 1) {
        memberBox.remove()
    } else {
        const inputs = memberBox.querySelectorAll('input')
        inputs.forEach(item => {
            item.value = ''
        })
    }
}

/**
 * Creates a new Member div and appends it.
 *
 * @return void
 */
function addNewMember() {
    // Grab the ID of our last member box, and find its index number
    const lastBox = document.querySelectorAll('#members .member-box:last-of-type')
    const indexPattern = /^member-(\d+)$/
    const matches = lastBox.id.match(indexPattern)
    if (!matches) {
        return
    }

    // Our member total is the last index + 1
    const memberTotal = parseInt(matches[1]) + 1

    // Create a new member box and give it the appropriate ID
    const newMember = document.createElement('div')
    newMember.classList.add('member-box')
    newMember.id = `member-${memberTotal}`

    // A new div to hold the fields
    const fieldsDiv = document.createElement('div')
    fieldsDiv.classList.add('member-fields')

    // Loop through our fields global to create the inputs in
    // .form-group divs.
    for (const field of fields) {
        const newField = document.createElement('div')
        newField.classList.add('form-group')

        const idString = `member-${field}-${memberTotal}`
        
        const newLabel = document.createElement('label')
        newLabel.setAttribute('for', idString)
        const labelStr = field.slice(0, 1).toUpperCase() + field.slice(1) + ':'
        const strong = document.createElement('strong')
        strong.innerHTML = labelStr
        newLabel.append(strong)
        newField.append(newLabel)

        const newInput = document.createElement('input')
        newInput.setAttribute('type', field == 'phone' ? 'tel' : 'text')
        newInput.setAttribute('id', idString)
        newInput.setAttribute('name', `member-${field}[]`)
        if (field == 'term') {
            newInput.setAttribute('maxlength', 9)
        }
        newField.append(newInput)

        fieldsDiv.append(newField)
    }

    // Append the new fields
    newMember.append(fieldsDiv)

    // Create the delete button
    const delButtonDiv = document.createElement('div')
    delButtonDiv.classList.add('delete-button-container')

    const newDeleteButton = document.createElement('button')
    newDeleteButton.setAttribute('id', `delete-${memberTotal}`)
    newDeleteButton.setAttribute('type', 'button')
    newDeleteButton.classList.add('button-secondary', 'delete')
    newDeleteButton.innerHTML = "&minus;"

    // Append the delete button
    delButtonDiv.append(newDeleteButton)
    newMember.append(delButtonDiv)

    // Add the new member just before the "Add Member" button
    const addButton = document.querySelector('#members .buttons')
    addButton.before(newMember)
}