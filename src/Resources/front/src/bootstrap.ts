
import './types/drupal';

import { AjaxLayoutHandler } from "./handler/ajax";
import { DrupalState } from "./bridge/drupal";
import { State } from "./state";

declare var document: Document;
declare var window: any;

if (window.Drupal) {
    let state: DrupalState;

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
} else {
    // Not in Drupal, bootstrap always
    let state = new State(new AjaxLayoutHandler('/', 'destinationwtf'));

    // @todo fix base path and destination parameteres
    state.init(document.body);
}
