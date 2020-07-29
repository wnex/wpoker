interface Data {
	action: string,
	type?: string,
	request_id?: string,
	params: object,
}

interface Listeners {
    [key:string]: Function;
}

export default class Socket {

	public socket !: WebSocket;
	private listeners : Listeners = {};

	private openFunc : Function = () => {};
	private closeFunc : Function = () => {};

	private requests : Data[] = [];
	private response : Listeners = {};

	private pingPong !: NodeJS.Timeout;
	private pingPongTimeout : number = 30;

	constructor (address : string, port? : number, reconnect : boolean = true) {
		if (port === undefined) {
			let parts = address.split(':');
			address = parts[0]+':'+parts[1];
			port = parseInt(parts[2]);
		}

		this.connect(address, port, reconnect);
	}

	public connect(address : string, port : number, reconnect : boolean = true) {
		this.socket = new WebSocket(address+":"+port);

		this.socket.addEventListener('open', () => {
			this.openFunc.call(this);

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
				this.closeFunc.call(this);
				setTimeout(() => this.connect(address, port, reconnect), 1000);
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

			if (
				this.listeners[data.action] !== undefined
			) {
				this.listeners[data.action].call(this, data);
			}
		});
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
		this.socket.send(JSON.stringify(data));
		this.setPongPongTimeout();
	}

	public listener(action : string, callback : Function) {
		this.listeners[action] = callback;
	}

	public request(action : string, params : any) : Promise<Data> {
		return new Promise((resolve, reject) => {
			let request_id = this.generateId(16),
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
		} else {
			this.openFunc = callback;
		}
	}

	public close(callback : Function) {
		clearInterval(this.pingPong);
		this.closeFunc = callback;
	}


	private dec2hex(dec : number) : string {
		return dec < 10
			? '0' + String(dec)
			: dec.toString(16)
	}

	// generateId :: Integer -> String
	public generateId(length : number) {
		var arr = new Uint32Array((length || 40) / 2);
		window.crypto.getRandomValues(arr);
		return Array.from(arr, this.dec2hex).join('').substr(0, length);;
	}

}
