import { v4 as uuidv4, v5 as uuidv5 } from 'uuid';

interface Data {
	action: string,
	type?: string,
	request_id?: string,
	params: object,
}

interface Listeners {
	[key:string]: Function[];
}

interface GroupListeners {
	[key:string]: Listeners;
}

interface Response {
	[key:string]: Function;
}

export default class Socket {
	public socket : WebSocket;
	public listeners : GroupListeners = {};
	protected currentGroup : string = 'nogroup';

	private openFuncs : Function[] = [];
	private closeFuncs : Function[] = [];

	private requests : Data[] = [];
	private response : Response = {};

	private pingPong !: NodeJS.Timeout;
	private pingPongTimeout : number = 30;

	constructor(address : string, port? : number, reconnect : boolean = true) {
		if (port === undefined) {
			let parts = address.split(':');
			address = parts[0]+':'+parts[1];
			port = parseInt(parts[2]);
		}

		this.socket = this.connect(address, port, reconnect);
	}

	public connect(address : string, port : number, reconnect : boolean = true) : WebSocket {
		this.socket = new WebSocket(address+":"+port);

		this.socket.addEventListener('open', () => {
			if (this.openFuncs.length > 0) {
				for (var i = 0; i < this.openFuncs.length; i++) {
					this.openFuncs[i].call(this);
				}
			}

			if (this.requests.length > 0) {
				for (var i = 0; i < this.requests.length; i++) {
					this.send(this.requests[i]);
				}
				this.requests = [];
			}

			this.setPongPongTimeout();
		}, false);

		this.socket.addEventListener('close', () => {
			if (this.closeFuncs.length > 0) {
				for (var i = 0; i < this.closeFuncs.length; i++) {
					this.closeFuncs[i].call(this);
				}
			}
			if (reconnect) {
				setTimeout(() => this.connect(address, port, reconnect), 1000);
			}
		}, false);

		this.socket.addEventListener('message', (etv) => {
			let data : Data = JSON.parse(etv.data);

			if (
				data.type === 'request' &&
				data.request_id !== undefined &&
				this.response[data.request_id] !== undefined
			) {
				this.response[data.request_id].call(this, data);
				delete this.response[data.request_id];
				return false;
			}

			for (let group in this.listeners) {
				let actionsList = this.listeners[group][data.action];
				if (actionsList !== undefined && actionsList.length > 0) {
					for (let i = 0; i < this.listeners[group][data.action].length; i++) {
						this.listeners[group][data.action][i].call(this, data);
					}
				}
			}
		});

		return this.socket;
	}

	private setPongPongTimeout() {
		clearTimeout(this.pingPong);
		this.pingPong = setTimeout(() => {
			this.send({
				type: 'ping',
			});
		}, this.pingPongTimeout * 1000);
	}

	public send(data : any) {
		if (this.isOpen()) {
			this.socket.send(JSON.stringify(data));
		} else {
			this.requests.push(data);
		}
		
		this.setPongPongTimeout();
	}

	public listener(action : string, callback : Function, group : string = '') {
		this.on(action, callback, group);
	}

	public on(action : string, callback : Function, group : string = '') {
		if (group === '') {
			group = this.currentGroup;
		}

		if (this.listeners[group] === undefined) {
			this.listeners[group] = {};
		}

		if (this.listeners[group][action] === undefined) {
			this.listeners[group][action] = [];
		}

		this.listeners[group][action].push(callback);
	}

	public off(action : string, callback : Function) {
		for (let group in this.listeners) {
			let actionsList = this.listeners[group][action];
			if (actionsList !== undefined && actionsList.length > 0) {
				for (let i = 0; i < actionsList.length; i++) {
					if (actionsList[i] === callback) {
						this.listeners[group][action].splice(i, 1);
						break;
					}
				}
			}
		}
	}

	public group(name : string) {
		this.currentGroup = name;
	}

	public endGroup() {
		this.currentGroup = 'nogroup';
	}

	public offGroup(name : string) {
		if (this.listeners[name] !== undefined) {
			this.listeners[name] = {};
		}
	}

	public request(action : string, params : any) : Promise<Data> {
		return new Promise((resolve, reject) => {
			let request_id = uuidv5('request', uuidv4()),
				data : Data = {
					'action': action,
					'type': 'request',
					'request_id': request_id,
					'params': params,
				};

			if (this.isOpen()) {
				this.send(data);
			} else {
				this.requests.push(data);
			}
			
			this.response[request_id] = (data : Data) => {
				resolve(data);
			};
		})
	}

	public isOpen() {
		return this.socket.readyState === this.socket.OPEN;
	}

	public onOpen(callback : Function) {
		if (this.isOpen()) {
			callback.call(this);
		}

		this.openFuncs.push(callback);
	}

	public onClose(callback : Function) {
		if (!this.isOpen()) {
			callback.call(this);
		}

		this.closeFuncs.push(callback);
	}

	public offOpen(callback : Function) {
		for (let i = 0; i < this.openFuncs.length; i++) {
			if (this.openFuncs[i] === callback) {
				this.openFuncs.splice(i, 1);
				break;
			}
		}
	}

	public offClose(callback : Function) {
		for (let i = 0; i < this.closeFuncs.length; i++) {
			if (this.closeFuncs[i] === callback) {
				this.closeFuncs.splice(i, 1);
				break;
			}
		}
	}

}
