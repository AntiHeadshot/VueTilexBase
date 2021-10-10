fetch("/api/v1.0/test/testClass?name=tes123zgerg5ergt1&surname=test2")
    .then(async(response) => {
        if (response.ok) {
            return response.json();
        } else {
            let responseJson = await response.json()
            throw responseJson;
        }
    })
    .then((responseJson) => {
        console.log(JSON.stringify(responseJson));
    })
    .catch((error) => {
        if (error instanceof Error)
            console.log(error.message + "\n" + error.Stack);
        else
            console.log(JSON.stringify(error));
    });

fetch("/api/v1.0/test2/testClass?name=tes445zgerg5ergt1&surname=test2")
    .then(async(response) => {
        if (response.ok) {
            return response.json();
        } else {
            let responseJson = await response.json()
            throw responseJson;
        }
    })
    .then((responseJson) => {
        console.log(JSON.stringify(responseJson));
    })
    .catch((error) => {
        if (error instanceof Error)
            console.log(error.message + "\n" + error.Stack);
        else
            console.log(JSON.stringify(error));
    });