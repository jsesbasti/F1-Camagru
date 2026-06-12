export async function api(url, method, body) {

	const options = {
		method,
		headers: {
			'Content-Type': 'application/json'
		}
	};

	if (body) {
		options.body = JSON.stringify(body);
	}

	const response = await fetch(url, options);

	return response;
}