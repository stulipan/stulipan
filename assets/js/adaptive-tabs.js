/**
 * --------------------------------------------------------------------------
 * adaptive-tabs.js
 * Version: 1.0.1
 * Dependencies: none
 *
 * Builds an adapting tabs with More button. It is a progressively
 * collapsing navigation menu.
 *
 * Logic from here: https://css-tricks.com/container-adapting-tabs-with-more-button/
 * --------------------------------------------------------------------------
 */

const Selector = {
  ADAPTIVE_WRAPPER  : '.adaptive-tabs',
  PRIMARY           : '.adaptive-primary',
  PRIMARY_ITEMS     : '.adaptive-primary > .adaptive-item:not(.adaptive-more)',
  SECONDARY         : '.adaptive-secondary',
  TEMPLATE          : '.adaptive-template',
  MORE              : '.adaptive-more',
  ITEM              : '.adaptive-item',
  MORE_BUTTON       : '.adaptive-button',
};
const ClassName = {
  ACTIVE            : '--active',
};

class AdaptiveTabs {
  constructor(container) {
    this.container = container;
    this.primary = container.querySelector(Selector.PRIMARY);
    this.primaryItems = container.querySelectorAll(Selector.PRIMARY_ITEMS);
    container.classList.add(ClassName.ACTIVE);

    this.template = container.querySelector(Selector.TEMPLATE);
    this.template.querySelector(Selector.SECONDARY).innerHTML = this.primary.innerHTML;

    this.primary.insertAdjacentHTML('beforeend', this.template.innerHTML);
    this.template.querySelector(Selector.SECONDARY).innerHTML = '';

    this.secondary = this.primary.querySelector(Selector.SECONDARY);
    this.secondaryItems = this.secondary.querySelectorAll(Selector.ITEM);
    this.allItems = this.primary.querySelectorAll(Selector.ITEM);
    this.moreLi = this.primary.querySelector(Selector.MORE);
    this.moreBtn = this.moreLi.querySelector(Selector.MORE_BUTTON);
    this.moreBtn.addEventListener('click', (e) => {
      e.preventDefault()
      container.classList.toggle('--show-secondary')
      this.moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'))
    });


    this.doAdapt();
    window.addEventListener('resize', this.doAdapt.bind(this));
    this.onOutsideClick();
  }

  doAdapt() {
    this.allItems.forEach((item) => {
      item.classList.remove('--hidden')
    })

    let stopWidth = this.moreBtn.offsetWidth
    let hiddenItems = []
    const primaryWidth = this.primary.offsetWidth
    this.primaryItems.forEach((item, i) => {
      let itemWidth = item.offsetWidth + parseInt(window.getComputedStyle(item).getPropertyValue('margin-left')) + + parseInt(window.getComputedStyle(item).getPropertyValue('margin-right'));
      if (primaryWidth >= stopWidth + itemWidth) {
        stopWidth += itemWidth
      } else {
        item.classList.add('--hidden')
        hiddenItems.push(i)
      }
    })

    if(!hiddenItems.length) {
      this.moreLi.classList.add('--hidden')
      this.container.classList.remove('--show-secondary')
      this.moreBtn.setAttribute('aria-expanded', false)
    }
    else {
      this.secondaryItems.forEach((item, i) => {
        if(!hiddenItems.includes(i)) {
          item.classList.add('--hidden')
        }
      })
    }
  }

  // It’s hiding the dropdown list automatically if user clicks anywhere outside the list.
  // For that we can bind a global click listener and check if the clicked element
  // or any of its parents is the secondarylist or “more” button. If not, the dropdown list gets dismissed.
  onOutsideClick() {
    document.addEventListener('click', (e) => {
      let el = e.target
      while(el) {
        if(el === this.secondary || el === this.moreBtn) {
          return;
        }
        el = el.parentNode
      }
      this.container.classList.remove('--show-secondary')
      this.moreBtn.setAttribute('aria-expanded', false)
    })
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const adaptiveContainers = document.querySelectorAll(Selector.ADAPTIVE_WRAPPER); // get DOM elements
  adaptiveContainers.forEach((container) => {
    new AdaptiveTabs(container);
  });
});


// export default new AdaptiveTabs()

// const instance = new AdaptiveTabs();
// export { instance as AdaptiveTabs };

export default AdaptiveTabs