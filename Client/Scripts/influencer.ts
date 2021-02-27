type Subscriptions = {
	[ticket: string]: {
		[id: string]: Function;
	};
};
class Influencer {
	private subscriptions: Subscriptions;

	constructor() {
		this.subscriptions = {};
	}

	public create(ticket: string = uid()) {
		this.subscriptions[ticket] = {};
		return ticket;
	}

	public subscribe(ticket: string, inbox: Function) {
		const id = uid();
		this.subscriptions[ticket][id] = inbox;
		return id;
	}

	public unsubscribe(ticket: string, id: string) {
		delete this.subscriptions?.[ticket]?.[id];
		if (Object.keys(this.subscriptions?.[ticket])?.length === 0) {
			delete this.subscriptions[ticket];
		}
	}

	public post(ticket: string, data: any) {
		for (const id in this.subscriptions?.[ticket]) {
			this.subscriptions[ticket][id](data);
		}
	}

	public destroy(ticket: string) {
		delete this.subscriptions?.[ticket];
	}
}
const influencer = new Influencer();
const createSubscription = influencer.create.bind(influencer);
const subscribe = influencer.subscribe.bind(influencer);
const unsubscribe = influencer.unsubscribe.bind(influencer);
const post = influencer.post.bind(influencer);
const destroySubscription = influencer.destroy.bind(influencer);
