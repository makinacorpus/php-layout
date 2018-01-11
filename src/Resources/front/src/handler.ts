
export interface StyleList {
    readonly current?: string;
    readonly styles: any; // Hash whose keys are style names, values are human readable names
}

// Interacts with the store
export interface LayoutHandler {
    debug(message: any): void;
    addColumn(token: string, layout: string, containerId: string, position?: number): Promise<Element>;
    addColumnContainer(token: string, layout: string, containerId: string, position?: number, columnCount?: number, style?: string): Promise<Element>;
    addItem(token: string, layout: string, containerId: string, itemType: string, itemId: string, position: number, style?: string): Promise<Element>;
    getAllowedStyles(token: string, layout: string, itemId: string): Promise<StyleList>;
    moveItem(token: string, layout: string, containerId: string, itemId: string, newPosition: number): Promise<void>;
    removeItem(token: string, layout: string, itemId: string): Promise<void>;
    renderItem(token: string, layout: string, itemId: string): Promise<Element>;
    setStyle(token: string, layout: string, itemId: string, style?: string): Promise<Element>;
}
