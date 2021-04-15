import { Controller } from 'stimulus';
import { useClickOutside } from 'stimulus-use';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets = ['count'];
    count = 0;

    connect() {
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
