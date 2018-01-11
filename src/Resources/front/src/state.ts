
import { Drake } from "dragula";
import { Container, ContainerType, getContainer, getItem } from "./item";
import { createMenu } from "./ui/menu";
import { LayoutHandler } from "./handler";

export class State {
    readonly handler: LayoutHandler;
    readonly containers: Container[] = [];
    readonly drake: Drake;

    constructor(handler: LayoutHandler) {
        this.handler = handler;

        this.drake = dragula({
            copy: (element: Element, source: Element): boolean => {
                try {
                    if (getContainer(source).readonly) {
                        return true;
                    }
                } catch (error) { // It may happen that we actually hit a non-container (source)
                    return true;
                }
                try {
                    return getItem(element).readonly
                } catch (error) {
                    return false;
                }
            },
            accepts: (element: Element, target: Element): boolean => {
                try {
                    return !getContainer(target).readonly;
                } catch (error) { // It may happen that we actually hit a non-container (source)
                    return false;
                }
            },
            invalid: (element: Element): boolean => {
                // This is not really documented in dragula or I could not find
                // it but when drag starts, it might start with any children in
                // the DOM, and we need to give dragula the right item to move
                // which may be any of the current element's parents.
                let current = element;
                while (current) {
                    if (current.hasAttribute("data-menu")) {
                        return true;
                    }
                    if (current.hasAttribute("data-item-id")) {
                        return false;
                    }
                    if (!current.parentElement) {
                        break;
                    }
                    current = current.parentElement;
                }
                return true;
            },
            revertOnSpill: true,
            removeOnSpill: false,
            direction: 'vertical'
        });

        // We need to add an extra callback to ensure that onDrop and onOver
        // class methods will not be applied to the event target and will keep
        // 'this' as a reference to the State class instance.
        this.drake.on('drop', (element: Element, target: Element, source: Element, sibling?: Element) => {
            this.onDrop(element, target, source, sibling);
        });

        this.drake.on('over', (element: Element, source: Element) => {
            this.onOver(element, source)
        });
    }

    translate(text: string, variables?: any): string {
        for (let name in variables) {
            text = text.replace(name, variables[name]);
        }
        return text;
    }

    // Terminate an element completly, at least from this API perspective.
    // This means we get rid of the element if it is in containers list but
    // also terminate the element from DOM by removing it from its parent.
    // Please note this will not call any kind of unitializer.
    remove(element: Element) {
        const index = this.drake.containers.indexOf(element);
        if (-1 !== index) {
            this.drake.containers.splice(index);
        }
        if (element.parentElement) {
            element.parentElement.removeChild(element);
        }
    }

    cancel(error?: any, element?: Element) {
        if (error) {
            this.handler.debug(error);
        }
        if (element) {
            element.remove();
        }
        this.drake.cancel(true);
    }

    onOver (element: Element, source: Element) {
        if (element instanceof HTMLElement) {
            element.style.cssFloat = 'none'; // Avoid visual glitches
        }
    }

    onDrop(element: Element, target: Element, source: Element, sibling?: Element) {
        try {
            const container = getContainer(target);
            const item = getItem(element);

            if (container.readonly) {
                throw `container is readonly`;
            }

            if (item.readonly) {
                this.handler.addItem(container.token, container.layoutId,
                    container.id, item.type, item.id, item.getPosition())
                .then(item => {
                    (<Element>element.parentElement).replaceChild(item, element);
                    this.initItem(item, container);
                    this.init(item);
                }) .catch(error => {
                    this.cancel(error, element);
                });
            } else {
                this.handler.moveItem(container.token, container.layoutId,
                    container.id, item.id, item.getPosition())
                .catch(error => {
                    this.cancel(error, element)
                });
            }
        } catch (error) {
            // This is run synchronously (not in a promise): drake.cancel()
            // method will correctly revert the operation, dragula won't have
            // run its cleanup() method yet
            this.cancel(error);
        }
    }

    // Collect new DOM information and initialize behaviours
    init(context: Element): void {
        this.collectLayouts(context);
        this.collectSources(context);
    }

    // Same as init() but force a single container initialization
    initContainer(element: Element, parent: Container) {

        if (element.hasAttribute("droppable")) {
            return; // already initialized
        }
        if (!element.hasAttribute("data-container") || !element.hasAttribute("data-id")) {
            return; // not a container
        }

        element.setAttribute("data-token", parent.token);
        element.setAttribute("data-layout-id", parent.layoutId);

        const container = getContainer(element);
        if (container.type !== ContainerType.Horizontal) {
            element.setAttribute("droppable", "1");
            this.drake.containers.push(container.element);
        }
        this.collectItems(container);

        createMenu(this, container);
    }

    initItem(element: Element, parent: Container) {

        if (element.hasAttribute("draggable")) {
            return; // already initialized
        }
        if (!element.hasAttribute("data-item-id")) {
            return; // not an item
        }

        const item = getItem(element);
        element.setAttribute("draggable", "1");

        if (!item.readonly) {
            createMenu(this, item);
        }
    }

    private collectSources(context: Element) {
        for (let source of <Element[]><any>context.querySelectorAll("[data-layout-source]")) {
            this.drake.containers.push(source);
        }
    }

    private collectItems(container: Container) {
        for (let element of <Element[]><any>container.element.childNodes) {
            if (element instanceof Element) {
                if (element.hasAttribute("data-item-id")) {
                    this.initItem(element, container);
                } else {
                    this.initContainer(element, container); // This is recursive
                }
            }
        }
    }

    private collectLayouts(context: Element) {
        for (let element of <Element[]><any>context.querySelectorAll("[data-layout]")) {

            if (!element.hasAttribute("data-token") || !element.hasAttribute("data-id")) {
                continue; // not a layout
            }

            const layout = getContainer(element);

            element.setAttribute("droppable", "1");
            this.drake.containers.push(layout.element);
            this.collectItems(layout);

            createMenu(this, layout);
        }
    }
}
