window.onload = function() {
    registerAddButton()
    registerDeleteButtons()
}

const fields = [
    'name',
    'dept',
    'phone',
    'term',
]

function registerAddButton() {
    const addButton = document.querySelector('#addButton')
    addButton.addEventListener('click', addNewMember)
}

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

function addNewMember() {
    const memberTotal = document.querySelectorAll('.member-box').length

    const newMember = document.createElement('div')
    newMember.classList.add('member-box')
    newMember.id = `member-${memberTotal}`

    const fieldsDiv = document.createElement('div')
    fieldsDiv.classList.add('member-fields')

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

    newMember.append(fieldsDiv)

    const delButtonDiv = document.createElement('div')
    delButtonDiv.classList.add('delete-button-container')

    const newDeleteButton = document.createElement('button')
    newDeleteButton.setAttribute('id', `delete-${memberTotal}`)
    newDeleteButton.setAttribute('type', 'button')
    newDeleteButton.classList.add('button-secondary', 'delete')
    newDeleteButton.innerHTML = "&minus;"

    delButtonDiv.append(newDeleteButton)
    newMember.append(delButtonDiv)

    const addButton = document.querySelector('#members .buttons')
    addButton.before(newMember)
}