
function findPosition(element: Element, attribute: string): number {
    let pos = 0;
    if (element.parentNode) {
        for (let sibling of <Node[]><any>element.parentNode.childNodes) {
            if (sibling instanceof Element) {
                // Do not filter with the data attribute from there because the item
                // might be dropped from another source than something we manage, and
                // it might not be replaced by the drop event (that will be done
                // later actually)
                if (element === sibling) {
                    break;
                }
                if (sibling.hasAttribute(attribute)) {
                    pos++;
                }
            }
        }
    }
    return pos;
}

function toggleClass(element: Element, cssClass: string) {
    if (element.classList.contains(cssClass)) {
        element.classList.remove(cssClass);
    } else {
        element.classList.add(cssClass);
    }
}

function findItemPosition(element: Element): number {
    return findPosition(element, "data-item-id");
}

function findContainerPosition(element: Element): number {
    return findPosition(element, "data-id");
}

export function getContainerCount(element: Element): number {
    let count = 0;
    for (let child of <Node[]><any>element.childNodes) {
        if (child instanceof Element) {
            if (child.hasAttribute("data-id") || child.hasAttribute("data-item-type")) {
                count++;
            }
        }
    }
    return count;
}

export enum ContainerType {
    Column = "vbox",
    Horizontal = "hbox",
    Layout = "Layout"
}

export function getContainer(element: Element): Container {
    if (element.hasAttribute("data-token") || element.hasAttribute("data-layout-id") || element.hasAttribute("data-id")) {
        return new Container(
            <string>element.getAttribute("data-id"),
            <string>element.getAttribute("data-container"),
            element,
            <string>element.getAttribute("data-token"),
            // If container has no data-layout-id, this means it is the layout
            <string>(element.getAttribute("data-layout-id") || element.getAttribute("data-id"))
        );
    }
    throw `element is not a container, or is not initialized properly`;
}

export function getItem(element: Element): Item {
    if (element.hasAttribute("data-item-id")) {
        return new Item(
            <string>(element.getAttribute("data-id") || element.getAttribute("data-item-id")),
            <string>(element.getAttribute("data-item-type") || "null"),
            !element.hasAttribute("data-id"),
            element
        );
    }
    throw `element is not an item`;
}

// Without a constructor function, and whithout using new we cannot use
// instanceof later (god I hate JavaScript); same goes for the Container
// class, and we need it.
export class Item {
    static DefaultStyle = "_default";

    readonly id: string;
    readonly type: string;
    readonly element: Element;
    readonly readonly: boolean;

    constructor(id: string, type: string, readonly: boolean, element: Element)
    {
        this.id = id;
        this.type = type;
        this.readonly = readonly;
        this.element = element;
    }

    getPosition() {
        // position cannot be cached, because item can be moved
        return findItemPosition(this.element);
    }

    toggleCollapse() {
        toggleClass(this.element, "collapsed");
    }

    private findParentElement(): Element {
        let current = this.element;
        while (current.parentElement) {
            current = current.parentElement;
            if (current.hasAttribute("data-id")) {
                return current;
            }
        }
        throw "Parent has not identifier";
    }

    getParentContainer(): Container {
        return getContainer(this.findParentElement());
    }

    findParentId(): string {
        return this.findParentElement().getAttribute("data-id") || "";
    }
}

export class Container extends Item {
    readonly layoutId: string;
    readonly token: string;

    constructor(id: string, type: string, element: Element,
        token: string, layoutId: string)
    {
        super(id, type, type === ContainerType.Horizontal, element);

        this.layoutId = layoutId;
        this.token = token;
    }

    getPosition() {
        // position cannot be cached, because item can be moved
        return findContainerPosition(this.element);
    }
}
