interface Data {
	action: string
}

interface Listeners {
    [key:string]: Function;
}

export default class Socket {

	public socket !: WebSocket;
	//private listeners : Function[] = [];
	private listeners : Listeners = {};

	private openFunc : Function = () => {};
	private closeFunc : Function = () => {};


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
				this.listeners[data.action] !== undefined
			) {
				this.listeners[data.action].call(this, data);
			}
		});
	}

	public send(data : any) {
		this.socket.send(JSON.stringify(data));
	}

	public listener(action : string, callback : Function) {
		this.listeners[action] = callback;
	}

	public isOpen() {
		return this.socket.readyState === this.socket.OPEN;
	}

	public open(callback : Function) {
		this.openFunc = callback;
	}

	public close(callback : Function) {
		this.closeFunc = callback;
	}

}
