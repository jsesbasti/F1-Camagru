document.addEventListener('DOMContentLoaded', async () => {
	const res = await fetch("http://localhost:9000/logged", {
		method: 'GET',
        credentials: "include"
    });
    const data = await res.json();
    if (data.logged === true) {
        document.getElementById("login-register").style.display = "none";
    }
});