
import { State } from "../state";

// Drupal specific state
export class DrupalState extends State {

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
