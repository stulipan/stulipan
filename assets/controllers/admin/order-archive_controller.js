import { Controller } from 'stimulus';
import { useClickOutside } from 'stimulus-use';

export default class extends Controller {
    static targets = ['count'];
    count = 0;

    connect() {
        console.log('order archive');
        // this.count = 0;
        useClickOutside(this);
    }

    incrementCounter() {
        this.count++;
        this.countTarget.innerText = this.count;

        // this.element.addEventListener('click', () => {
        //     this.count++;
        //     this.countTarget.innerText = this.count;
        // });

    }

    clickOutside(event) {
        this.countTarget.innerText = this.count--;
    }
}
