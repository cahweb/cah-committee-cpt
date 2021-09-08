window.onload = function() {
    addSelectListener()
}

function addSelectListener() {
    const select = document.querySelector('#committee-select')
    select.addEventListener('change', event => {
        changeCommittee(event.target.value)
    })
}

function changeCommittee(index) {
    console.log("changing to index " + index)
    const tbodies = document.querySelectorAll('.committee-list')
    tbodies.forEach(item => {
        if (item.id == `committee-${index}`) {
            item.dataset.active = 1
            item.classList.remove('d-none');
        } else {
            item.dataset.active = 0
            item.classList.add('d-none');
        }
    })
    const excerpts = document.querySelectorAll('.excerpt')
    excerpts.forEach(item => {
        if(item.id == `excerpt-${index}`) {
            item.classList.remove('d-none')
        } else {
            item.classList.add('d-none')
        }
    })
}