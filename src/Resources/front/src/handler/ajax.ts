
import { LayoutHandler, StyleList } from "../handler";
import { Item } from "../item";

enum AjaxRoute {
    Add = 'layout/ajax/add-item',
    AddColumn = 'layout/ajax/add-column',
    AddColumnContainer = 'layout/ajax/add-column-container',
    GetAllowedStyles = 'layout/ajax/get-styles',
    Move = 'layout/ajax/move',
    Remove = 'layout/ajax/remove',
    Render = 'layout/ajax/render',
    SetStyle = 'layout/ajax/set-style',
}

// AJAX store, suitable for all web apps as long as they implement the generic
// php-layout controller, using the route described in the AjaxRoute enum.
// It could even be used for anything that's non PHP, only the HTTP API is
// revelant.
export class AjaxLayoutHandler implements LayoutHandler {

    readonly baseUrl: string;
    readonly destination?: string;

    constructor(baseUrl: string, destination? : string) {
        this.baseUrl = baseUrl;
        this.destination = destination;
    }

    private buildFormData(data: any): FormData {
        const formData = new FormData();
        for (let key in data) {
            formData.append(key, data[key]);
        }
        return formData;
    }

    private request(route: string, data: any): Promise<XMLHttpRequest> {
        return new Promise<XMLHttpRequest>((resolve: (req: XMLHttpRequest) => void, reject: (err: any) => void) => {
            let req = new XMLHttpRequest();
            req.open('POST', this.baseUrl + route);
            req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            req.addEventListener("load", function () {
                if (this.status !== 200) {
                    reject(`${this.status}: ${this.statusText}`)
                } else {
                    resolve(req);
                }
            });
            req.addEventListener("error", function () {
                reject(`${this.status}: ${this.statusText}`);
            });
            req.send(this.buildFormData(data));
        });
    }

    private createElementFromResponse(req: XMLHttpRequest): Element {
        const data = JSON.parse(req.responseText);
        if (!data || !data.success || !data.output) {
            throw `${req.status}: ${req.statusText}: got invalid response data`;
        }

        const element = document.createElement('div');
        element.innerHTML = data.output;

        if (!(element.firstElementChild instanceof Element)) {
            throw `${req.status}: ${req.statusText}: got invalid response html output`;
        }

        return element.firstElementChild;
    }

    debug(message: any): void {
        console.log(`layout error: ${message}`);
    }

    async addColumn(token: string, layout: string, containerId: string, position?: number): Promise<Element> {
        const req = await this.request(AjaxRoute.AddColumn, {
            token: token,
            layout: layout,
            containerId: containerId,
            position: position || 0,
            destination: this.destination
        });

        return this.createElementFromResponse(req);
    }

    async addColumnContainer(token: string, layout: string, containerId: string, position?: number, columnCount?: number, style?: string): Promise<Element> {
        const req = await this.request(AjaxRoute.AddColumnContainer, {
            token: token,
            layout: layout,
            containerId: containerId,
            position: position,
            columnCount: columnCount || 2,
            style: style || Item.DefaultStyle,
            destination: this.destination
        });

        return this.createElementFromResponse(req);
    }

    async addItem(token: string, layout: string, containerId: string, itemType: string, itemId: string, position: number, style?: string): Promise<Element> {
        const req = await this.request(AjaxRoute.Add, {
            token: token,
            layout: layout,
            containerId: containerId,
            itemType: itemType,
            itemId: itemId,
            position: position,
            style: style || Item.DefaultStyle,
            destination: this.destination
        });

        return this.createElementFromResponse(req);
    }

    async getAllowedStyles(token: string, layout: string, itemId: string): Promise<StyleList> {
        const req = await this.request(AjaxRoute.GetAllowedStyles, {
            token: token,
            layout: layout,
            itemId: itemId,
            destination: this.destination
        });

        const data = JSON.parse(req.responseText);
        if (!data || !data.success || !data.styles) {
            throw `${req.status}: ${req.statusText}: got invalid response data`;
        }

        const ret: StyleList = {current: data.current || null, styles: data.styles};

        return ret;
    }

    async moveItem(token: string, layout: string, containerId: string, itemId: string, newPosition: number): Promise<void> {
        await this.request(AjaxRoute.Move, {
            token: token,
            layout: layout,
            containerId: containerId,
            itemId: itemId,
            newPosition: newPosition,
            destination: this.destination
        });
    }

    async removeItem(token: string, layout: string, itemId: string): Promise<void> {
        await this.request(AjaxRoute.Remove, {
            token: token,
            layout: layout,
            itemId: itemId,
            destination: this.destination
        });
    }

    async renderItem(token: string, layout: string, itemId: string): Promise<Element> {
        const req = await this.request(AjaxRoute.Render, {
            token: token,
            layout: layout,
            itemId: itemId,
            destination: this.destination
        });

        return this.createElementFromResponse(req);
    }

    async setStyle(token: string, layout: string, itemId: string, style?: string): Promise<Element> {
        const req = await this.request(AjaxRoute.SetStyle, {
            token: token,
            layout: layout,
            itemId: itemId,
            style: style || null,
            destination: this.destination
        });

        return this.createElementFromResponse(req);
    }
}
