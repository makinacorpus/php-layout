
const DIALOG_TEMPLATE =
`<div class="layout-modal" tabindex="-1" role="dialog">
  <div role="document">
    <button name="close" type="button" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title">__TITLE__</h4>
    <div id="content"></div>
  </div>
</div>`;

// Create a modal whose promise acts when modal is closed
export function createModal(title: string, content: Element, posX: number, posY: number): Promise<any> {
    return new Promise<void>((resolve: () => void, reject: (err: any) => void) => {

        const temp = document.createElement('div');
        temp.innerHTML = DIALOG_TEMPLATE.replace(new RegExp('__TITLE__', 'g'), title);

        const dialog = <HTMLElement>temp.firstElementChild;
        const placeholder = <HTMLElement>dialog.querySelector("#content");
        (<HTMLElement>placeholder.parentElement).replaceChild(content, placeholder);

        document.body.appendChild(dialog);
        dialog.style.display = "block";
        dialog.style.position = "absolute";
        dialog.style.left = posX.toString() + "px";
        dialog.style.top = posY.toString() + "px";
        dialog.style.transform = "translate(-50%, -50%)";
        dialog.classList.add("open");

        (<HTMLElement>dialog.querySelector("button[name=close]")).addEventListener("click", (event: MouseEvent) => {
            event.preventDefault();
            dialog.remove();
            resolve();
        });
    });
}
