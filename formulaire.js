document.addEventListener("DOMContentLoaded", () => {
  const sourceSelect = document.getElementById("source");
  const linkField = document.getElementById("link-field");
  const mediaUploadField = document.getElementById("media-upload-field");
  const otherFields = document.getElementById("other-fields");
  const form = document.getElementById("alertForm");
  const confirmationPage = document.getElementById("confirmationPage");
  const recapContent = document.getElementById("recap-content");
  const successMessage = document.getElementById("successMessage");
  const editButton = document.getElementById("editButton");
  const confirmButton = document.getElementById("confirmButton");
  const fileInput = document.getElementById("mediaUpload");
  const previewContainer = document.getElementById("preview-container");
  const filePreview = document.getElementById("file-preview");
  const fileNameDisplay = document.getElementById("file-name");

  // Affichage conditionnel selon la source choisie
  function handleSourceChange() {
    const value = sourceSelect.value;
    if (value === "Autres") {
      linkField.classList.add("hidden");
      otherFields.classList.remove("hidden");
      mediaUploadField.classList.remove("hidden");
    } else if (value !== "") {
      linkField.classList.remove("hidden");
      otherFields.classList.add("hidden");
      mediaUploadField.classList.remove("hidden");
    } else {
      linkField.classList.add("hidden");
      otherFields.classList.add("hidden");
      mediaUploadField.classList.add("hidden");
    }
  }

  // Attacher la fonction handleSourceChange à l'événement onchange du select
  sourceSelect.addEventListener("change", handleSourceChange);

  // Initialiser le formulaire selon la source sélectionnée (au cas où il y a une valeur par défaut)
  handleSourceChange();

  fileInput.addEventListener("change", function () {
    const file = this.files[0];

    if (file) {
      fileNameDisplay.textContent = file.name;
      previewContainer.classList.remove("hidden");

      // Si c'est une image
      if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
          filePreview.innerHTML = `<img src="${e.target.result}" alt="Aperçu" class="object-cover w-full h-full">`;
        };
        reader.readAsDataURL(file);
      }
      // Si c'est une vidéo
      else if (file.type.startsWith("video/")) {
        filePreview.innerHTML = `<video class="object-cover w-full h-full" muted>
                                    <source src="${URL.createObjectURL(
                                      file
                                    )}" type="${file.type}">
                                  </video>`;
      } else {
        filePreview.innerHTML = `<span class="text-gray-500 text-xs">Pas d’aperçu</span>`;
      }
    } else {
      previewContainer.classList.add("hidden");
      filePreview.innerHTML = "";
      fileNameDisplay.textContent = "";
    }
  });

  // Fonction de création du récapitulatif
  function createRecap(data) {
    recapContent.innerHTML = "";
    for (const [key, value] of Object.entries(data)) {
      if (value) {
        const div = document.createElement("div");
        let displayKey = key;

        // Améliorer l'affichage des clés
        switch (key) {
          case "information":
            displayKey = "Ce que vous avez vu ou entendu";
            break;
          case "mainSubject":
            displayKey = "Sujet principal";
            break;
          case "content":
            displayKey = "Contenu exact";
            break;
          case "source":
            displayKey = "Source";
            break;
          case "source-date":
            displayKey = "Date de l'information";
            break;
          case "lien":
            displayKey = "Lien";
            break;
          case "author":
            displayKey = "Auteur";
            break;
          case "nameOrAlias":
            displayKey = "Nom ou Pseudo";
            break;
          case "contactInfo":
            displayKey = "Contact";
            break;
          case "mediaUpload":
            displayKey = "Image ou Vidéo";
            break;
        }

        div.innerHTML = `<strong>${displayKey} :</strong> ${value}`;
        recapContent.appendChild(div);
      }
    }
  }

  // Validation des champs requis
  function validateForm(formData) {
    const requiredFields = ["information", "mainSubject", "content"];

    const sourceValue = formData.get("source");

    if (sourceValue === "Autres") {
      requiredFields.push("source-name");
    } else if (sourceValue !== "") {
      requiredFields.push("lien");
    }

    for (const field of requiredFields) {
      if (!formData.get(field)) {
        alert("Veuillez remplir tous les champs obligatoires.");
        return false;
      }
    }
    return true;
  }

  // Soumission du formulaire
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    // Validation des champs requis
    if (!validateForm(formData)) {
      return;
    }

    // Construction des données à afficher dans le récapitulatif
    const recapData = {};
    formData.forEach((value, key) => {
      if (value) {
        recapData[key] = value;
      }
    });

    // Masquer le formulaire et afficher le récapitulatif
    form.classList.add("hidden");
    confirmationPage.classList.remove("hidden");
    createRecap(recapData);
  });

  // Bouton pour modifier les données
  editButton.addEventListener("click", () => {
    confirmationPage.classList.add("hidden");
    form.classList.remove("hidden");
  });

  // Bouton pour confirmer et envoyer
  confirmButton.addEventListener("click", () => {
    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
      data[key] = value;
    });
    // Show loading state
    confirmButton.disabled = true;
    confirmButton.innerHTML =
      '<svg class="w-5 h-5 mr-3 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enregistrement en cours...';
    // Ne pas formater les clés - utiliser les noms exacts des champs
    // Envoi des données via fetch
    fetch("index.html", {
      // Chemin relatif corrigé
      method: "POST",
      body: JSON.stringify(data),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => {
        console.log("Response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("Response data:", data);
        if (data.success) {
          successMessage.classList.remove("hidden");
          confirmationPage.classList.add("hidden");
          console.log("Formulaire soumis avec succès.");
        } else {
          alert(
            "Une erreur s'est produite lors de l'envoi du formulaire: " +
              (data.message || "")
          );
          console.error("Erreur serveur:", data);
        }
      })
      .catch((error) => {
        console.error("Erreur lors de l'envoi :", error);
        alert("Une erreur est survenue. Veuillez réessayer.");
      });
  });
});
