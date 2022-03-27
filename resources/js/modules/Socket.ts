import { v4 as uuidv4, v5 as uuidv5 } from 'uuid';

interface Data {
	action: string,
	type?: string,
	request_id?: string,
	params: object,
}

interface Listeners {
    [key:string]: Function;
}

interface Response {
    [key:string]: Function;
}

export default class Socket {
	public socket : WebSocket;
	private listeners : Listeners = {};

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

		if (reconnect) {
			this.socket.addEventListener('close', () => {
				if (this.closeFuncs.length > 0) {
					for (var i = 0; i < this.closeFuncs.length; i++) {
						this.closeFuncs[i].call(this);
					}
				}
				this.connect(address, port, reconnect);
			}, false);
		}

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

			if (this.listeners[data.action] !== undefined) {
				this.listeners[data.action].call(this, data);
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

	public listener(action : string, callback : Function) {
		this.listeners[action] = callback;
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

	public open(callback : Function) {
		if (this.isOpen()) {
			callback.call(this);
		}

		this.openFuncs.push(callback);
	}

	public close(callback : Function) {
		if (!this.isOpen()) {
			callback.call(this);
		}

		this.closeFuncs.push(callback);
	}

}
