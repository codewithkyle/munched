class EventBusController {
    constructor() {
        this.subscriptions = {};
    }
    create(ticket = this.uid()) {
        if (ticket in this.subscriptions) {
            console.warn(`A subscription has already been created for: ${ticket}`);
        }
        else {
            this.subscriptions[ticket] = {};
        }
        return ticket;
    }
    subscribe(ticket, inbox) {
        const id = this.uid();
        this.subscriptions[ticket][id] = inbox;
        return id;
    }
    unsubscribe(id, ticket = null) {
        var _a, _b, _c, _d;
        if (ticket === null) {
            for (const subTicket in this.subscriptions) {
                for (const inboxId in this.subscriptions[subTicket]) {
                    if (id === inboxId) {
                        ticket = subTicket;
                        break;
                    }
                }
                if (ticket !== null) {
                    break;
                }
            }
        }
        (_b = (_a = this.subscriptions) === null || _a === void 0 ? void 0 : _a[ticket]) === null || _b === void 0 ? true : delete _b[id];
        if (((_d = Object.keys((_c = this.subscriptions) === null || _c === void 0 ? void 0 : _c[ticket])) === null || _d === void 0 ? void 0 : _d.length) === 0) {
            delete this.subscriptions[ticket];
        }
    }
    post(ticket, data) {
        var _a;
        for (const id in (_a = this.subscriptions) === null || _a === void 0 ? void 0 : _a[ticket]) {
            this.subscriptions[ticket][id](data);
        }
    }
    destroy(ticket) {
        var _a;
        (_a = this.subscriptions) === null || _a === void 0 ? true : delete _a[ticket];
    }
    uid() {
        return new Array(4)
            .fill(0)
            .map(() => Math.floor(Math.random() * Number.MAX_SAFE_INTEGER).toString(16))
            .join("-");
    }
}
const EventBus = new EventBusController();
