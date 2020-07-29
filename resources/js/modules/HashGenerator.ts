export default class HashGenerator {
	public generate(length : number) : string {
		var arr = new Uint32Array((length || 40) / 2);
		window.crypto.getRandomValues(arr);
		return Array.from(arr, this.dec2hex).join('').substr(0, length);;
	}

	private dec2hex(dec : number) : string {
		return dec < 10
			? '0' + String(dec)
			: dec.toString(16)
	}
}
