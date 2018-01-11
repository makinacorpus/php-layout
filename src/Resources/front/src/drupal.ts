
import './types/drupal';

import { AjaxLayoutHandler } from "./handler/ajax";
import { State } from "./state";

let state: DrupalState;

// Drupal specific state
class DrupalState extends State {

    // Init override, attach Drupal behaviours on DOM element init
    init(context: Element): void {
        super.init(context);
        Drupal.attachBehaviors(context);
    }

    // Use Drupal translation system
    translate(text: string, variables?: any): string {
        return Drupal.t(text, variables);
    }

    // init() variant that does not call Drupal behaviours
    initNoBehaviors(context: Element): void {
        super.init(context);
    }
}

Drupal.behaviors.Layout = {
    attach: (context: Element, settings: any) => {
        if (!settings.layout) {
            settings.layout = {};
        }
        if (!state) {
            state = new DrupalState(new AjaxLayoutHandler(settings.basePath, settings.layout.destination));
        }
        // Initial init call must not call Drupal.attachBehaviors
        // else we would have an inifinite loop, that would be pretty
        // much very bad isn't it?
        state.initNoBehaviors(context);
    }
};
