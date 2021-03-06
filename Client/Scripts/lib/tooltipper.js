class Tooltipper {
    constructor() {
        this.showTooltip = (e) => {
            const el = e.currentTarget;
            let text = el.getAttribute("tooltip");
            if (!text.length) {
                text = el.getAttribute("aria-label");
            }
            if (!text.length) {
                console.warn(`Tooltip could not be created -- missing aria-label or tooltip attribute values.`);
                return;
            }
            const existingToolitp = document.body.querySelector(`tool-tip[uid="${el.dataset.tooltipUid}"]`);
            if (existingToolitp) {
                existingToolitp.remove();
            }
            const tooltip = document.createElement("tool-tip");
            tooltip.setAttribute("uid", el.dataset.tooltipUid);
            tooltip.innerHTML = text;
            tooltip.setAttribute("role", "tooltip");
            const elBounds = el.getBoundingClientRect();
            tooltip.style.position = "absolute";
            tooltip.style.zIndex = "999999";
            tooltip.style.opacity = "0";
            document.body.appendChild(tooltip);
            const tipBounds = tooltip.getBoundingClientRect();
            let tooltipLeft = elBounds.left + elBounds.width / 2 - tipBounds.width / 2;
            if (tooltipLeft + tipBounds.width > window.innerWidth - 4) {
                const diff = tooltipLeft + tipBounds.width - window.innerWidth + 4;
                tooltipLeft -= diff;
            }
            else if (tooltipLeft < 4) {
                tooltipLeft = 4;
            }
            let tooltipTop = elBounds.top + elBounds.height;
            if (tooltipTop + tipBounds.height > window.innerHeight - 4) {
                tooltipTop = elBounds.top - tipBounds.height;
            }
            tooltip.style.top = `${tooltipTop}px`;
            tooltip.style.left = `${tooltipLeft}px`;
            tooltip.classList.add("visible");
            tooltip.style.opacity = "1";
        };
        this.hideTooltip = (e) => {
            const el = e.currentTarget;
            const tooltip = document.body.querySelector(`tool-tip[uid="${el.dataset.tooltipUid}"]`);
            tooltip === null || tooltip === void 0 ? void 0 : tooltip.remove();
        };
        this.elements = [];
        this.nextUid = 0;
        this.addTooltips();
    }
    purgeTooltips() {
        const elements = Array.from(document.body.querySelectorAll("[tooltip]"));
        for (let k = this.elements.length - 1; k >= 0; k--) {
            let survived = false;
            for (let i = 0; i < elements.length; i++) {
                if (elements[i].dataset.tooltipUid === this.elements[k].dataset.tooltipUid) {
                    survived = true;
                    break;
                }
            }
            if (!survived) {
                this.elements[k].removeEventListener("mouseenter", this.showTooltip);
                this.elements[k].removeEventListener("focus", this.showTooltip);
                this.elements[k].removeEventListener("mouseleave", this.hideTooltip);
                this.elements[k].removeEventListener("blur", this.hideTooltip);
                this.elements.splice(k, 1);
            }
        }
    }
    addTooltips() {
        const newElements = Array.from(document.body.querySelectorAll("[tooltip]:not([data-tooltip-uid])"));
        if (newElements.length) {
            for (let i = 0; i < newElements.length; i++) {
                newElements[i].dataset.tooltipUid = `${this.nextUid}`;
                this.nextUid++;
                newElements[i].addEventListener("mouseenter", this.showTooltip);
                newElements[i].addEventListener("focus", this.showTooltip);
                newElements[i].addEventListener("mouseleave", this.hideTooltip);
                newElements[i].addEventListener("blur", this.hideTooltip);
            }
        }
        this.elements = [...this.elements, ...newElements];
        this.purgeTooltips();
    }
    refresh() {
        this.addTooltips();
    }
}
const globalTooltipper = new Tooltipper();
const refresh = globalTooltipper.refresh.bind(globalTooltipper);
setInterval(refresh, 500);
