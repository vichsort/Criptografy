export function processForm(e, cript) {
    e.preventDefault()
    const form = e.target
  
    const formData = new FormData(form)
    const value = formData.get("value")

    if (cript == true) {
        console.log("estamos encriptando");
        encript(value);
    } else {
        console.log("estamos decriptando");
        decript(value);
    }
}

function encript(val) {
    console.log(val);

}

function decript(val) {
    console.log(val);
    
}