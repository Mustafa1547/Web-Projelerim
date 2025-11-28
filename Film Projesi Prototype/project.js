
const form = document.getElementById("film-form");
const titleElement = document.querySelector("#title");
const directorElement = document.querySelector("#director");
const urlElement = document.querySelector("#url");
const cardbody=document.querySelectorAll(".card-body")[1];
const clear=document.getElementById("clear-films");




// UI Objesini Başlatma
const ui = new UI();

//Storage Objesini Üret
const storage=new Storage();

// Tüm eventleri yükleme

eventListeners();

function eventListeners(){
    form.addEventListener("submit",addFilm);
    document.addEventListener("DOMContentLoaded",function(){
        let films=storage.getFilmFromStorage();
        ui.loadALLFilms(films);

    });
    cardbody.addEventListener("click",deleteFilm);
    clear.addEventListener("click",clearAllFilms);
   

    }



function addFilm(e){
    const title = titleElement.value;
    const director = directorElement.value;
    const url = urlElement.value;

    if (title === "" || director === "" || url === ""){
        // Hata 
      ui.displayMessages("tüm alanları doldurun...","danger");

    }
    else {
        // Yeni Film
        const newFilm = new Film(title,director,url);

        ui.addFilmToUI(newFilm);  // Arayüze film ekleme
        ui.displayMessages("film başarıyla eklendi...","success");
        storage.addFilmToStorage(newFilm);
        
    e.preventDefault();
      
    }

    ui.clearInput(titleElement.urlElement,directorElement);


    e.preventDefault();
  
   
}
function deleteFilm(e){
    if(e.target.id==="delete-film"){
        ui.deleteFilmFromUI(e.target);
        storage.deleteFilmFromStorage(e.target.parentElement.previousElementSibling.previousElementSibling.textContent);

        ui.displayMessages("silme işlemi başarılı...","success");
        
        
    }
}
function clearAllFilms(){
    if(confirm("Emin misiniz ?")){
    
    ui.clearAllFilmsFromUI();
    storage.clearAllFilmsFromStorage();
}
}