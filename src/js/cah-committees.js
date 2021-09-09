window.onload = function() {
    addSelectListener()
}

/**
 * Adds an event listener to the Committee select box, calling our
 * changeCommittee() function, below.
 *
 * @return void
 */
function addSelectListener() {
    const select = document.querySelector('#committee-select')
    select.addEventListener('change', event => {
        changeCommittee(event.target.value)
    })
}

/**
 * Changes the displayed committee on the page. Not fancy, but
 * gets the job done.
 *
 * @param {Number} index The value of the committee option from the
 *                       select box.
 *
 * @return void
 */
function changeCommittee(index) {
    // Loop through all our tbody elements, displaying the one
    // we want
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
    // Do the same for our excerpts
    const excerpts = document.querySelectorAll('.excerpt')
    excerpts.forEach(item => {
        if(item.id == `excerpt-${index}`) {
            item.classList.remove('d-none')
        } else {
            item.classList.add('d-none')
        }
    })
}