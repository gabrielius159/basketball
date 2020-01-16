require('../css/homepage.css');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

const tabItems = document.querySelectorAll('.tab-item');
const tabContentItems = document.querySelectorAll('.tab-content-item');

// Event action

function selectItem(e) {
    removeBorder();
    removeShow();

    // Add the border to the current tab
    this.classList.add('tab-border');

    // Grab content item from DOM

    const tabContentItem = document.querySelector(`#${this.id}-content`);

    // Add show class

    tabContentItem.classList.add('show');
}

// Remove border for all tabs

function removeBorder() {
    tabItems.forEach(item => item.classList.remove('tab-border'));
}

// Hide all contents

function removeShow() {
    tabContentItems.forEach(item => item.classList.remove('show'));
}

// Listen for tab click

tabItems.forEach(item => item.addEventListener('click', selectItem));

