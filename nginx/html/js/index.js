const cookies = cookieStore.getAll();

document.addEventListener('DOMContentLoaded', async () => {
	(await cookies).forEach( ( value ) => {
		if (value.name === 'PHPSESSID')
			document.getElementById('login-register').style.display = none;
	})
});