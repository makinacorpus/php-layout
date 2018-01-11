
import { Container, ContainerType, Item, getContainerCount } from "../item";
import { State } from "../state";
import { createModal } from "./dialog";
import { StyleList } from "../handler";

// @todo move left, move right, move up, move down, in menus

const ICON_TEMPLATE = `<span class="fa fa-__GLYPH__" aria-hidden="true"></span> `;
const DRAG_TEMPLATE =
`<span role="drag" title="Maintain left mouse button to move">
  <span class="fa fa-arrows" aria-hidden="true"></span>
</span>`;

const MENU_TEMPLATE =
`<div class="layout-menu" data-menu="1">
  <a role="button" href="#" title="Click to open, double-click to expand/hide content">
    <span class="fa fa-cog" aria-hidden="true"></span>
    <span class="title">__TITLE__</span>
  </a>
  <ul></ul>
</div>`;

// @todo unregister menus when elements/columns are dropped
let globalMenuRegistry: Menu[] = [];
let globalDocumentListenerSet = false;

function globalDocumentCloseMenuListener(event: MouseEvent) {
    if (globalMenuRegistry.length) {
        for (let menu of globalMenuRegistry) {
            if (!(event.target instanceof Node) || !menu.element.contains(event.target)) {
                menu.close();
            }
        }
    }
}

function globalDocumentCloseAllMenu() {
    if (globalMenuRegistry.length) {
        for (let menu of globalMenuRegistry) {
            menu.close();
        }
    }
}

class Menu {
    readonly item: Item;
    readonly element: Element;
    readonly master: Element;

    constructor(item: Item, element: Element) {
        this.item = item;
        this.element = element;
        this.master = (<Element>element.querySelector("a"));
        this.master.addEventListener("dblclick", (event: MouseEvent) => {
            event.stopPropagation();
            this.item.toggleCollapse()
        });
        this.master.addEventListener("click", (event: MouseEvent) => {
            event.preventDefault();
            this.open();
        });
    }

    close(): void {
        const dropdown = this.element.querySelector("ul");
        if (dropdown) {
            dropdown.style.display = "none";
        }
    }

    open(): void {
        const dropdown = this.element.querySelector("ul");
        if (dropdown) {
            dropdown.style.display = "block";
        }
    }
}

function createLink(state: State, text: string, icon: void | string, callback: (event: MouseEvent) => Promise<any>): Element {
    const menuItem = document.createElement("li");

    const link = document.createElement("a");
    link.setAttribute("href", "#");
    link.setAttribute("role", "button");

    if (icon) {
        link.innerHTML += ICON_TEMPLATE.replace("__GLYPH__", icon);
    }
    link.innerHTML += text;

    link.addEventListener("click", function (event: MouseEvent) {
        event.preventDefault();
        event.stopPropagation();

        globalDocumentCloseAllMenu();

        // @todo loader
        callback(event).then(_ => {
            // ok
        }).catch(error => {
            // not ok
            state.handler.debug(error);
        });
        // @todo end loader
    });

    menuItem.appendChild(link);

    return menuItem;
}

function createDivider(): Element {
    const divider = document.createElement('li');
    divider.setAttribute("class", "divider");
    divider.setAttribute("role", "separator");
    return divider;
}

function createItemLinks(state: State, item: Item): Element[] {
    const links: Element[] = [];
    const parent = item.getParentContainer();

    links.push(createLink(state, state.translate("Change style"), "wrench", (event: MouseEvent): Promise<any> => {

        let currentSelection: undefined | string;
        let hasChanged = false;

        return state.handler.getAllowedStyles(parent.token, parent.layoutId, item.id).then((styleList: StyleList) => {
            const content = document.createElement("form");
            const select = document.createElement("select");
            content.appendChild(select);

            let hasDefault = false;
            for (let style in styleList.styles) {
                let option = <HTMLOptionElement>document.createElement("option");
                option.value = style;
                option.innerHTML = styleList.styles[style];
                select.appendChild(option);
                if (style === Item.DefaultStyle) {
                    hasDefault = true;
                }
                if (styleList.current && style === styleList.current) {
                    option.selected = true;
                }
            }
            if (!hasDefault) {
                let option = <HTMLOptionElement>document.createElement("option");
                option.value = Item.DefaultStyle;
                option.innerHTML = state.translate("Default");
                select.insertBefore(option, select.firstElementChild);
            }

            select.addEventListener("change", () => {
                currentSelection = select.value;
                hasChanged = true;
            });

            createModal(state.translate("Set style"), content, event.pageX, event.pageY).then((): any => {
                if (hasChanged) {
                    return state.handler.setStyle(parent.token, parent.layoutId, item.id, currentSelection).then((element: Element) => {
                        (<Element>item.element.parentElement).replaceChild(element, item.element);
                        state.init(element);
                        state.initItem(element, parent);
                    });
                }
            });
        })
    }));

    links.push(createDivider());

    links.push(createLink(state, state.translate("Remove"), "remove", () => {
        return state.handler.removeItem(parent.token, parent.layoutId, item.id).then(() => {
            state.remove(item.element);
        });
    }));

    return links;
}

function createHorizontalLinks(state: State, container: Container): Element[] {
    const links: Element[] = [];

    links.push(createLink(state, state.translate("Add column to left"), "chevron-left", () => {
        return state.handler.addColumn(container.token, container.layoutId, container.id, 0).then(element => {
            container.element.insertBefore(element, container.element.firstChild);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    links.push(createLink(state, state.translate("Add column to right"), "chevron-right", () => {
        const position = getContainerCount(container.element);
        return state.handler.addColumn(container.token, container.layoutId, container.id, position).then(element => {
            container.element.appendChild(element);
            state.init(element);
            state.initContainer(element, container);
        });
    }));

    links.push(createDivider());

    links.push(createLink(state, state.translate("Remove"), "remove", () => {
        return state.handler.removeItem(container.token, container.layoutId, container.id).then(() => {
            state.remove(container.element);
        });
    }));

    return links;
}

function createLayoutLinks(state: State, container: Container): Element[] {
    const links: Element[] = [];

    // options: wrench layout/callback/edit-item (itemId)

    // links.push(createDivider());

    // prepend column container: th-large layout/ajax/add-column-container (containerId, position = 0, columnCount = 2)

    links.push(createLink(state, state.translate("Add columns to top"), "columns", () => {
        return state.handler.addColumnContainer(container.token, container.layoutId, container.id, 0).then(element => {
            container.element.insertBefore(element, container.element.firstChild);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    links.push(createLink(state, state.translate("Add columns to bottom"), "columns", () => {
        const position = getContainerCount(container.element);
        return state.handler.addColumn(container.token, container.layoutId, container.id, position).then(element => {
            container.element.appendChild(element);
            state.init(element);
            state.initContainer(element, container);
        });
    }));

    // append column container: th-large layout/ajax/add-column-container (containerId, position = length, columnCount = 2)

    // links.push(createDivider());

    // prepend item: picture layout/callback/add-item (containerId, position = 0)
    // append item: picture layout/callback/add-item (containerId, position = length)
    // set page content here: star

    return links;
}

function createColumnLinks(state: State, container: Container): Element[] {
    const links = createLayoutLinks(state, container);
    const parent = container.getParentContainer();

    links.push(createDivider());

    links.push(createLink(state, state.translate("Change style"), "wrench", (event: MouseEvent): Promise<any> => {

        let currentSelection: undefined | string;
        let hasChanged = false;

        return state.handler.getAllowedStyles(parent.token, parent.layoutId, container.id).then((styleList: StyleList) => {
            const content = document.createElement("form");
            const select = document.createElement("select");
            content.appendChild(select);

            let hasDefault = false;
            for (let style in styleList.styles) {
                let option = <HTMLOptionElement>document.createElement("option");
                option.value = style;
                option.innerHTML = styleList.styles[style];
                select.appendChild(option);
                if (style === Item.DefaultStyle) {
                    hasDefault = true;
                }
                if (styleList.current && style === styleList.current) {
                    option.selected = true;
                }
            }
            if (!hasDefault) {
                let option = <HTMLOptionElement>document.createElement("option");
                option.value = Item.DefaultStyle;
                option.innerHTML = state.translate("Default");
                select.insertBefore(option, select.firstElementChild);
            }

            select.addEventListener("change", () => {
                currentSelection = select.value;
                hasChanged = true;
            });

            createModal(state.translate("Set style"), content, event.pageX, event.pageY).then((): any => {
                if (hasChanged) {
                    return state.handler.setStyle(parent.token, parent.layoutId, container.id, currentSelection).then((element: Element) => {
                        (<Element>container.element.parentElement).replaceChild(element, container.element);
                        state.init(element);
                        state.initContainer(element, parent);
                    });
                }
            });
        })
    }));

    links.push(createDivider());

    links.push(createLink(state, state.translate("Add column before"), "chevron-left", () => {
        return state.handler.addColumn(parent.token, parent.layoutId, parent.id, container.getPosition()).then(element => {
            parent.element.insertBefore(element, container.element);
            state.init(element);
            state.initContainer(element, parent);
        });
    }));
    links.push(createLink(state, state.translate("Add column after"), "chevron-right", () => {
        return state.handler.addColumn(parent.token, parent.layoutId, parent.id, container.getPosition() + 1).then(element => {
            parent.element.insertBefore(element, container.element.nextSibling);
            state.init(element);
            state.initContainer(element, parent);
        });
    }));

    links.push(createDivider());

    links.push(createLink(state, state.translate("Remove this column"), "remove", () => {
        return state.handler.removeItem(container.token, container.layoutId, container.id).then(() => {
            state.remove(container.element);
        });
    }));

    return links;
}

// Create menu for the given item
export function createMenu(state: State, item: Item): void {

    let links: Element[] = [];
    let title: string = state.translate("Error");
    let addDragIcon = false;

    if (item instanceof Container) {
        if (item.type === ContainerType.Column) {
            title = state.translate("Column");
            links = createColumnLinks(state, item);
        } else if (item.type === ContainerType.Horizontal) {
            title = state.translate("Columns container");
            links = createHorizontalLinks(state, item);
            addDragIcon = true;
        } else {
            title = state.translate("Layout");
            links = createLayoutLinks(state, item);
        }
    } else {
        title = state.translate("Item");
        links = createItemLinks(state, item);
        addDragIcon = true;
    }

    // Use a RegExp to handle multiple occurences (using a raw string
    // does not work and only replaces the very first).
    let output = MENU_TEMPLATE.replace(new RegExp('__TITLE__', 'g'), title).replace("__LINKS__", "<li><a>coucou</a></href>");
    let element = document.createElement('div');
    element.innerHTML = output;

    let parentElement = <Element>element.firstElementChild;
    let menuList = (<HTMLElement>parentElement.querySelector("ul"));

    // Add links
    for (let link of links) {
        menuList.appendChild(link);
    }

    if (!globalDocumentListenerSet) {
        document.addEventListener("click", (event: MouseEvent) => {
            globalDocumentCloseMenuListener(event);
        });
        globalDocumentListenerSet = true;
    }

    globalMenuRegistry.push(new Menu(item, parentElement));
    item.element.insertBefore(parentElement, item.element.firstChild);

//    if (addDragIcon) {
//        let dragElement: Element = document.createElement('div');
//        dragElement.innerHTML = DRAG_TEMPLATE;
//        dragElement = <Element>dragElement.firstElementChild;
//        item.element.insertBefore(dragElement, item.element.firstChild);
//    }
}
