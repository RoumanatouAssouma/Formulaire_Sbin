        // Form state
        const formState = {
            currentStep: 1,
            totalSteps: 3,
            showRecap: false,
            formData: {
                statut: "nouvelle",
                dateObservation: "",
                dateNotification: "",
                canalPrimaire: "",
                autreCanalPrimaire: "",
                liens: "",
                auteur: "",
                objetPrincipal: "",
                propos: "",
                niveauAlerte: "",
                impactPotentiel: [],
                autreImpact: "",
                actionsImmédiates: "",
                propositions: "",
                responsable: "",
                notes: "",
            },
        }

        // DOM Elements
        document.addEventListener("DOMContentLoaded", () => {
            // DOM Elements
            const stepTitle = document.getElementById("step-title")
            const stepDescription = document.getElementById("step-description")
            const stepIndicator = document.getElementById("step-indicator")
            const progressPercentage = document.getElementById("progress-percentage")
            const progressBar = document.getElementById("progress-bar")
            const prevButton = document.getElementById("prev-button")
            const nextButton = document.getElementById("next-button")
            const submitContainer = document.getElementById("submit-container")
            const stepNavigation = document.getElementById("step-navigation")
            const alertForm = document.getElementById("alert-form")
            const mainForm = document.getElementById("main-form")
            const successMessage = document.getElementById("success-message")
            const backToFormButton = document.getElementById("back-to-form")
            const submitButton = document.getElementById("submit-button")
            const downloadPdfButton = document.getElementById("download-pdf-button")
            const downloadWordButton = document.getElementById("download-word-button")

            // Step containers
            const step1Container = document.getElementById("step-1")
            const step2Container = document.getElementById("step-2")
            const step3Container = document.getElementById("step-3")
            const recapContainer = document.getElementById("recap")

            // Special fields containers
            const autreCanalContainer = document.getElementById("autre-canal-container")
            const autreImpactContainer = document.getElementById("autre-impact-container")

            // Recap sections
            const recapIdentification = document.getElementById("recap-identification")
            const recapAnalyse = document.getElementById("recap-analyse")
            const recapGestion = document.getElementById("recap-gestion")

            // Event listeners for conditional fields
            document.getElementById("canalPrimaire").addEventListener("change", function () {
                if (this.value === "autre") {
                    autreCanalContainer.classList.remove("hidden")
                } else {
                    autreCanalContainer.classList.add("hidden")
                }
            })

            document.getElementById("impact-autre").addEventListener("change", function () {
                if (this.checked) {
                    autreImpactContainer.classList.remove("hidden")
                } else {
                    autreImpactContainer.classList.add("hidden")
                }
            })

            // Navigation event listeners
            prevButton.addEventListener("click", previousStep)
            nextButton.addEventListener("click", nextStep)
            backToFormButton.addEventListener("click", backToForm)
            alertForm.addEventListener("submit", submitForm)
            downloadPdfButton.addEventListener("click", generatePDF)
            downloadWordButton.addEventListener("click", generateWord)

            // Function to update UI based on current step
            function updateUI() {
                // Update progress bar
                const progressValue = formState.showRecap ? 100 : (formState.currentStep / formState.totalSteps) * 100
                progressBar.style.width = `${progressValue}%`
                progressPercentage.textContent = `${Math.round(progressValue)}%`

                // Update step title and description
                if (formState.showRecap) {
                    stepTitle.textContent = "Récapitulatif"
                    stepDescription.textContent = "Vérifiez les informations avant envoi"
                    stepIndicator.textContent = "Récap"

                    // Show recap and hide other steps
                    step1Container.classList.add("hidden")
                    step2Container.classList.add("hidden")
                    step3Container.classList.add("hidden")
                    recapContainer.classList.remove("hidden")

                    // Show submit button and hide next button
                    stepNavigation.classList.add("hidden")
                    submitContainer.classList.remove("hidden")

                    // Populate recap data
                    updateRecapView()
                } else {
                    // Reset submit button visibility
                    stepNavigation.classList.remove("hidden")
                    submitContainer.classList.add("hidden")

                    // Hide all steps first
                    step1Container.classList.add("hidden")
                    step2Container.classList.add("hidden")
                    step3Container.classList.add("hidden")
                    recapContainer.classList.add("hidden")

                    // Show current step
                    document.getElementById(`step-${formState.currentStep}`).classList.remove("hidden")

                    // Update step indicator
                    stepIndicator.textContent = `Étape ${formState.currentStep}/${formState.totalSteps}`

                    // Update step title and description based on current step
                    switch (formState.currentStep) {
                        case 1:
                            stepTitle.textContent = "Identification"
                            stepDescription.textContent = "Informations de base sur l'alerte"
                            nextButton.innerHTML = 'Suivant <i data-lucide="chevron-right" class="w-4 h-4">➡️</i>'
                            break
                        case 2:
                            stepTitle.textContent = "Analyse"
                            stepDescription.textContent = "Détails et implications de la situation"
                            nextButton.innerHTML = 'Suivant <i data-lucide="chevron-right" class="w-4 h-4">➡️</i>'
                            break
                        case 3:
                            stepTitle.textContent = "Gestion"
                            stepDescription.textContent = "Actions et suivi"
                            // On last step, change next button text
                            nextButton.innerHTML = 'Voir le récapitulatif <i data-lucide="chevron-right" class="w-4 h-4">➡️</i>'
                            break
                    }
                }

                // Enable/disable previous button
                if (formState.currentStep === 1 && !formState.showRecap) {
                    prevButton.disabled = true
                    prevButton.classList.add("opacity-50", "cursor-not-allowed")
                } else {
                    prevButton.disabled = false
                    prevButton.classList.remove("opacity-50", "cursor-not-allowed")
                }
            }

            // Function to populate recap data
            function updateRecapView() {
                // Generate HTML for each section
                const identificationHTML = `
                <div class="space-y-2">
                    <p><strong>Statut:</strong> ${getStatusLabel(formState.formData.statut)}</p>
                    <p><strong>Date d'observation:</strong> ${formState.formData.dateObservation || "Non renseigné"}</p>
                    <p><strong>Date de notification:</strong> ${formState.formData.dateNotification || "Non renseigné"}</p>
                    <p><strong>Canal primaire:</strong> ${getCanalLabel(formState.formData.canalPrimaire)}</p>
                    ${formState.formData.canalPrimaire === "autre" ? `<p><strong>Précision canal:</strong> ${formState.formData.autreCanalPrimaire || "Non précisé"}</p>` : ""}
                    <p><strong>Liens:</strong> ${formState.formData.liens || "Non renseigné"}</p>
                    <p><strong>Auteur:</strong> ${formState.formData.auteur || "Non renseigné"}</p>
                </div>
        `

                const analyseHTML = `
                <div class="space-y-2">
                    <p><strong>Objet principal:</strong> ${formState.formData.objetPrincipal || "Non renseigné"}</p>
                    <p><strong>Propos:</strong> ${formState.formData.propos || "Non renseigné"}</p>
                    <p><strong>Niveau d'alerte:</strong> ${getNiveauLabel(formState.formData.niveauAlerte)}</p>
                    <p><strong>Impact potentiel:</strong> ${formState.formData.impactPotentiel.length > 0 ? formState.formData.impactPotentiel.map((impact) => getImpactLabel(impact)).join(", ") : "Non renseigné"}</p>
                    ${formState.formData.impactPotentiel.includes("autre") ? `<p><strong>Autre impact:</strong> ${formState.formData.autreImpact || "Non précisé"}</p>` : ""}
                </div>
            `

                const gestionHTML = `
                    <div class="space-y-2">
                        <p><strong>Actions immédiates:</strong> ${formState.formData.actionsImmédiates || "Non renseigné"}</p>
                        <p><strong>Propositions:</strong> ${formState.formData.propositions || "Non renseigné"}</p>
                        <p><strong>Responsable:</strong> ${formState.formData.responsable || "Non renseigné"}</p>
                        <p><strong>Notes:</strong> ${formState.formData.notes || "Non renseigné"}</p>
                    </div>
        `

                // Update recap sections
                recapIdentification.innerHTML = identificationHTML
                recapAnalyse.innerHTML = analyseHTML
                recapGestion.innerHTML = gestionHTML
            }

            // Helper functions to get human-readable labels
            function getStatusLabel(status) {
                const statusMap = {
                    nouvelle: "Nouvelle",
                    "en-cours": "En cours d'analyse",
                    "plan-action": "Plan d'action défini",
                    "sous-controle": "Sous contrôle",
                    cloturee: "Clôturée",
                }
                return statusMap[status] || status
            }

            function getCanalLabel(canal) {
                const canalMap = {
                    "reseaux-sociaux": "Réseaux Sociaux",
                    "media-en-ligne": "Média en ligne",
                    "media-traditionnel": "Média traditionnel (TV/Radio/Presse)",
                    messagerie: "Messagerie (WhatsApp/SMS)",
                    conversation: "Conversation / Bouche-à-oreille",
                    client: "Client (via Service Client)",
                    autre: "Autre",
                }
                return canalMap[canal] || canal || "Non renseigné"
            }

            function getNiveauLabel(niveau) {
                const niveauMap = {
                    critique: "Critique (Niveau 4)",
                    eleve: "Élevé (Niveau 3)",
                    moyen: "Moyen (Niveau 2)",
                    faible: "Faible (Niveau 1)",
                }
                return niveauMap[niveau] || niveau || "Non renseigné"
            }

            function getImpactLabel(impact) {
                const impactMap = {
                    reputation: "Réputation de la marque",
                    confiance: "Confiance des clients",
                    ventes: "Ventes / Abonnements",
                    relations: "Relations avec les partenaires / régulateurs",
                    moral: "Moral des employés",
                    inconnu: "Inconnu à ce stade",
                    autre: "Autre",
                }
                return impactMap[impact] || impact
            }

            // Function to collect form data from current step
            function collectFormData() {
                switch (formState.currentStep) {
                    case 1:
                        // Get selected status
                        const statusRadios = document.querySelectorAll('input[name="statut"]')
                        for (const radio of statusRadios) {
                            if (radio.checked) {
                                formState.formData.statut = radio.value
                                break
                            }
                        }

                        formState.formData.dateObservation = document.getElementById("dateObservation").value
                        formState.formData.dateNotification = document.getElementById("dateNotification").value
                        formState.formData.canalPrimaire = document.getElementById("canalPrimaire").value
                        if (formState.formData.canalPrimaire === "autre") {
                            formState.formData.autreCanalPrimaire = document.getElementById("autreCanalPrimaire").value
                        }
                        formState.formData.liens = document.getElementById("liens").value
                        formState.formData.auteur = document.getElementById("auteur").value
                        break
                    case 2:
                        formState.formData.objetPrincipal = document.getElementById("objetPrincipal").value
                        formState.formData.propos = document.getElementById("propos").value

                        // Get selected niveau d'alerte
                        const niveauRadios = document.querySelectorAll('input[name="niveauAlerte"]')
                        for (const radio of niveauRadios) {
                            if (radio.checked) {
                                formState.formData.niveauAlerte = radio.value
                                break
                            }
                        }

                        // Handle checkbox group for impactPotentiel
                        formState.formData.impactPotentiel = []
                        document.querySelectorAll('input[name="impactPotentiel"]:checked').forEach((checkbox) => {
                            formState.formData.impactPotentiel.push(checkbox.value)
                        })

                        if (document.getElementById("impact-autre").checked) {
                            formState.formData.autreImpact = document.getElementById("autreImpact").value
                        }
                        break
                    case 3:
                        formState.formData.actionsImmédiates = document.getElementById("actionsImmédiates").value
                        formState.formData.propositions = document.getElementById("propositions").value
                        formState.formData.responsable = document.getElementById("responsable").value
                        formState.formData.notes = document.getElementById("notes").value
                        break
                }
            }

            // Function to validate current step
            function validateCurrentStep() {
                let isValid = true
                const requiredFields = []

                switch (formState.currentStep) {
                    case 1:
                        // Validate required fields for step 1
                        if (!document.getElementById("dateObservation").value) {
                            document.getElementById("dateObservation-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("dateObservation-error").classList.remove("hidden")
                            requiredFields.push("Date d'observation")
                            isValid = false
                        } else {
                            document.getElementById("dateObservation-error").classList.add("hidden")
                        }

                        if (!document.getElementById("dateNotification").value) {
                            document.getElementById("dateNotification-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("dateNotification-error").classList.remove("hidden")
                            requiredFields.push("Date de notification")
                            isValid = false
                        } else {
                            document.getElementById("dateNotification-error").classList.add("hidden")
                        }

                        if (!document.getElementById("canalPrimaire").value) {
                            document.getElementById("canalPrimaire-error").textContent = "Veuillez sélectionner un canal"
                            document.getElementById("canalPrimaire-error").classList.remove("hidden")
                            requiredFields.push("Canal primaire")
                            isValid = false
                        } else {
                            document.getElementById("canalPrimaire-error").classList.add("hidden")
                        }

                        if (
                            document.getElementById("canalPrimaire").value === "autre" &&
                            !document.getElementById("autreCanalPrimaire").value
                        ) {
                            document.getElementById("autreCanalPrimaire-error").textContent = "Veuillez préciser le canal"
                            document.getElementById("autreCanalPrimaire-error").classList.remove("hidden")
                            requiredFields.push("Précision du canal primaire")
                            isValid = false
                        } else if (document.getElementById("canalPrimaire").value === "autre") {
                            document.getElementById("autreCanalPrimaire-error").classList.add("hidden")
                        }
                        break
                    case 2:
                        // Validate required fields for step 2
                        if (!document.getElementById("objetPrincipal").value) {
                            document.getElementById("objetPrincipal-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("objetPrincipal-error").classList.remove("hidden")
                            requiredFields.push("Objet principal")
                            isValid = false
                        } else {
                            document.getElementById("objetPrincipal-error").classList.add("hidden")
                        }

                        if (!document.getElementById("propos").value) {
                            document.getElementById("propos-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("propos-error").classList.remove("hidden")
                            requiredFields.push("Propos incriminés")
                            isValid = false
                        } else {
                            document.getElementById("propos-error").classList.add("hidden")
                        }

                        // Check if any niveau d'alerte is selected
                        const niveauSelected = Array.from(document.querySelectorAll('input[name="niveauAlerte"]')).some(
                            (radio) => radio.checked,
                        )
                        if (!niveauSelected) {
                            document.getElementById("niveauAlerte-error").textContent = "Veuillez sélectionner un niveau d'alerte"
                            document.getElementById("niveauAlerte-error").classList.remove("hidden")
                            requiredFields.push("Niveau d'alerte")
                            isValid = false
                        } else {
                            document.getElementById("niveauAlerte-error").classList.add("hidden")
                        }

                        // Check if any impact potentiel is selected
                        const impactSelected = Array.from(document.querySelectorAll('input[name="impactPotentiel"]')).some(
                            (checkbox) => checkbox.checked,
                        )
                        if (!impactSelected) {
                            document.getElementById("impactPotentiel-error").textContent =
                                "Veuillez sélectionner au moins un impact potentiel"
                            document.getElementById("impactPotentiel-error").classList.remove("hidden")
                            requiredFields.push("Impact potentiel")
                            isValid = false
                        } else {
                            document.getElementById("impactPotentiel-error").classList.add("hidden")
                        }

                        if (document.getElementById("impact-autre").checked && !document.getElementById("autreImpact").value) {
                            document.getElementById("autreImpact-error").textContent = "Veuillez préciser l'autre impact"
                            document.getElementById("autreImpact-error").classList.remove("hidden")
                            requiredFields.push("Précision de l'autre impact")
                            isValid = false
                        } else if (document.getElementById("impact-autre").checked) {
                            document.getElementById("autreImpact-error").classList.add("hidden")
                        }
                        break
                    case 3:
                        // Validate required fields for step 3
                        if (!document.getElementById("propositions").value) {
                            document.getElementById("propositions-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("propositions-error").classList.remove("hidden")
                            requiredFields.push("Propositions")
                            isValid = false
                        } else {
                            document.getElementById("propositions-error").classList.add("hidden")
                        }

                        if (!document.getElementById("responsable").value) {
                            document.getElementById("responsable-error").textContent = "Ce champ est obligatoire"
                            document.getElementById("responsable-error").classList.remove("hidden")
                            requiredFields.push("Responsable")
                            isValid = false
                        } else {
                            document.getElementById("responsable-error").classList.add("hidden")
                        }
                        break
                }

                return isValid
            }

            // Navigation functions
            function nextStep() {
                // Validate current step before proceeding
                if (!validateCurrentStep()) {
                    return
                }

                // Collect data from current step
                collectFormData()

                if (formState.currentStep < formState.totalSteps) {
                    // Move to next step
                    formState.currentStep++
                    updateUI()
                } else {
                    // Show recap if we're on the last step
                    formState.showRecap = true
                    updateUI()
                }
            }

            function previousStep() {
                if (formState.showRecap) {
                    // Go back from recap to last step
                    formState.showRecap = false
                    updateUI()
                } else if (formState.currentStep > 1) {
                    // Move to previous step
                    formState.currentStep--
                    updateUI()
                }
            }

            function backToForm() {
                // Go back from recap to last step
                formState.showRecap = false
                updateUI()
            }

            // Form submission
            async function submitForm(e) {
                e.preventDefault()

                // Show loading state
                submitButton.disabled = true
                submitButton.innerHTML =
                    '<svg class="w-5 h-5 mr-3 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enregistrement en cours...'

                try {
                    // Envoyer les données à la base de données via PHP
                    const response = await fetch("/Formulaire/submit_alerte.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(formState.formData),
                    })

                    const result = await response.json()

                    if (result.success) {
                        // Show success message
                        mainForm.classList.add("hidden")
                        successMessage.classList.remove("hidden")
                    } else {
                        throw new Error(result.message || "Erreur lors de l'enregistrement")
                    }
                } catch (error) {
                    console.error("Erreur lors de l'enregistrement:", error)
                    alert("Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.")
                } finally {
                    // Reset submit button
                    submitButton.disabled = false
                    submitButton.innerHTML = 'Enregistrer la fiche <i data-lucide="check-circle-2" class="w-4 h-4">✅</i>'
                }
            }


            // Fonction pour générer le PDF
            async function generatePDF() {
                // PAS besoin de "import"
                html2canvas(document.querySelector("#elementAImprimer")).then(canvas => {
                    document.body.appendChild(canvas)
                })


                // Créer une copie du récapitulatif pour le PDF
                const pdfContent = document.createElement("div")
                pdfContent.innerHTML = recapContainer.innerHTML
                pdfContent.style.padding = "20px"
                pdfContent.style.backgroundColor = "white"
                pdfContent.style.width = "210mm" // Format A4
                pdfContent.style.color = "black"

                // Ajouter temporairement à la page mais caché
                pdfContent.style.position = "absolute"
                pdfContent.style.left = "-9999px"
                document.body.appendChild(pdfContent)

                try {
                    // Utiliser html2canvas pour convertir le contenu en image
                    const canvas = await html2canvas(pdfContent, {
                        scale: 2, // Meilleure qualité
                        useCORS: true,
                        logging: false,
                    })

                    // Créer un nouveau document PDF (format A4)
                    const { jsPDF } = window.jspdf
                    const pdf = new jsPDF("p", "mm", "a4")

                    // Ajouter un titre
                    pdf.setFontSize(18)
                    pdf.text("Fiche de Veille & d'Alerte DTD", 105, 15, { align: "center" })

                    // Ajouter la date de génération
                    const today = new Date()
                    const dateStr = today.toLocaleDateString("fr-FR")
                    pdf.setFontSize(10)
                    pdf.text(`Document généré le ${dateStr}`, 105, 22, { align: "center" })

                    // Ajouter l'image du canvas au PDF
                    const imgData = canvas.toDataURL("image/png")
                    const imgWidth = 190
                    const imgHeight = (canvas.height * imgWidth) / canvas.width

                    // Ajouter l'image avec un peu d'espace en haut
                    pdf.addImage(imgData, "PNG", 10, 30, imgWidth, imgHeight)

                    // Si le contenu est trop grand, ajouter des pages supplémentaires
                    if (imgHeight > 250) {
                        let heightLeft = imgHeight
                        let position = 30

                        // Supprimer la première page qui serait incomplète
                        pdf.deletePage(1)

                        // Recréer la première page avec le titre
                        pdf.addPage()
                        pdf.setFontSize(18)
                        pdf.text("Fiche de Veille & d'Alerte DTD", 105, 15, { align: "center" })
                        pdf.setFontSize(10)
                        pdf.text(`Document généré le ${dateStr}`, 105, 22, { align: "center" })

                        // Ajouter l'image par morceaux sur plusieurs pages
                        while (heightLeft > 0) {
                            position = heightLeft - imgHeight
                            pdf.addImage(imgData, "PNG", 10, position + 30, imgWidth, imgHeight)
                            heightLeft -= 250

                            if (heightLeft > 0) {
                                pdf.addPage()
                            }
                        }
                    }

                    // Générer un nom de fichier avec la date
                    const fileName = `Fiche_Alerte_DTD_${today.getFullYear()}${(today.getMonth() + 1).toString().padStart(2, "0")}${today.getDate().toString().padStart(2, "0")}.pdf`

                    // Télécharger le PDF
                    pdf.save(fileName)

                    // Nettoyer
                    document.body.removeChild(pdfContent)

                    return true
                } catch (error) {
                    // Nettoyer en cas d'erreur
                    if (document.body.contains(pdfContent)) {
                        document.body.removeChild(pdfContent)
                    }
                    throw error
                }
            }

            // Fonction alternative pour générer un PDF plus simple
            function generateSimplePDF() {
                // Créer un nouveau document PDF (format A4)
                const { jsPDF } = window.jspdf
                const pdf = new jsPDF("p", "mm", "a4")

                // Ajouter un titre
                pdf.setFontSize(18)
                pdf.text("Fiche de Veille & d'Alerte DTD", 105, 15, { align: "center" })

                // Ajouter la date de génération
                const today = new Date()
                const dateStr = today.toLocaleDateString("fr-FR")
                pdf.setFontSize(10)
                pdf.text(`Document généré le ${dateStr}`, 105, 22, { align: "center" })

                // Définir la police pour le contenu
                pdf.setFontSize(12)

                let yPosition = 30
                const lineHeight = 7
                const margin = 10
                const pageWidth = 210 - margin * 2

                // Fonction pour ajouter du texte avec retour à la ligne
                function addWrappedText(text, y) {
                    const textLines = pdf.splitTextToSize(text, pageWidth - 20)
                    pdf.text(textLines, margin + 10, y)
                    return y + lineHeight * textLines.length
                }

                // Fonction pour ajouter une section
                function addSection(title, content, y) {
                    // Vérifier s'il reste assez d'espace sur la page
                    if (y > 270) {
                        pdf.addPage()
                        y = 20
                    }

                    pdf.setFont(undefined, "bold")
                    pdf.text(title, margin, y)
                    pdf.setFont(undefined, "normal")

                    y += lineHeight

                    // Ajouter chaque élément du contenu
                    for (const item of content) {
                        const itemText = `${item.label}: ${item.value}`
                        y = addWrappedText(itemText, y)
                        y += lineHeight / 2
                    }

                    return y + lineHeight
                }

                // Préparer les données pour chaque section
                const identificationData = [
                    { label: "Statut", value: getStatusLabel(formState.formData.statut) },
                    { label: "Date d'observation", value: formState.formData.dateObservation || "Non renseigné" },
                    { label: "Date de notification", value: formState.formData.dateNotification || "Non renseigné" },
                    { label: "Canal primaire", value: getCanalLabel(formState.formData.canalPrimaire) },
                ]

                if (formState.formData.canalPrimaire === "autre") {
                    identificationData.push({
                        label: "Précision canal",
                        value: formState.formData.autreCanalPrimaire || "Non précisé",
                    })
                }

                identificationData.push(
                    { label: "Liens", value: formState.formData.liens || "Non renseigné" },
                    { label: "Auteur", value: formState.formData.auteur || "Non renseigné" },
                )

                const analyseData = [
                    { label: "Objet principal", value: formState.formData.objetPrincipal || "Non renseigné" },
                    { label: "Propos", value: formState.formData.propos || "Non renseigné" },
                    { label: "Niveau d'alerte", value: getNiveauLabel(formState.formData.niveauAlerte) },
                    {
                        label: "Impact potentiel",
                        value:
                            formState.formData.impactPotentiel.length > 0
                                ? formState.formData.impactPotentiel.map((impact) => getImpactLabel(impact)).join(", ")
                                : "Non renseigné",
                    },
                ]

                if (formState.formData.impactPotentiel.includes("autre")) {
                    analyseData.push({ label: "Autre impact", value: formState.formData.autreImpact || "Non précisé" })
                }

                const gestionData = [
                    { label: "Actions immédiates", value: formState.formData.actionsImmédiates || "Non renseigné" },
                    { label: "Propositions", value: formState.formData.propositions || "Non renseigné" },
                    { label: "Responsable", value: formState.formData.responsable || "Non renseigné" },
                    { label: "Notes", value: formState.formData.notes || "Non renseigné" },
                ]

                // Ajouter les sections au PDF
                yPosition = addSection("Identification de l'Alerte", identificationData, yPosition)
                yPosition = addSection("Analyse de l'Alerte", analyseData, yPosition)
                yPosition = addSection("Gestion de l'Alerte", gestionData, yPosition)

                // Générer un nom de fichier avec la date
                const fileName = `Fiche_Alerte_DTD_${today.getFullYear()}${(today.getMonth() + 1).toString().padStart(2, "0")}${today.getDate().toString().padStart(2, "0")}.pdf`

                // Télécharger le PDF
                pdf.save(fileName)

                return true
            }


            // Fonction pour générer le document Word
            async function generateWord() {
                try {
                    // Afficher un indicateur de chargement
                    downloadWordButton.disabled = true
                    downloadWordButton.innerHTML =
                        '<svg class="w-5 h-5 mr-3 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Génération du document Word...'

                    // Accéder à la bibliothèque docx
                    const { Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, BorderStyle } = docx

                    // Créer un nouveau document
                    const doc = new Document({
                        sections: [
                            {
                                properties: {},
                                children: [
                                    // Titre
                                    new Paragraph({
                                        text: "Fiche de Veille & d'Alerte DTD",
                                        heading: HeadingLevel.HEADING_1,
                                        alignment: AlignmentType.CENTER,
                                        spacing: {
                                            after: 200,
                                        },
                                    }),

                                    // Date de génération
                                    new Paragraph({
                                        text: `Document généré le ${new Date().toLocaleDateString("fr-FR")}`,
                                        alignment: AlignmentType.CENTER,
                                        spacing: {
                                            after: 400,
                                        },
                                    }),

                                    // Section Identification
                                    new Paragraph({
                                        text: "Identification de l'Alerte",
                                        heading: HeadingLevel.HEADING_2,
                                        spacing: {
                                            before: 400,
                                            after: 200,
                                        },
                                        border: {
                                            bottom: {
                                                color: "auto",
                                                space: 1,
                                                style: BorderStyle.SINGLE,
                                                size: 6,
                                            },
                                        },
                                    }),

                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Statut: ", bold: true }),
                                            new TextRun(getStatusLabel(formState.formData.statut) || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Date d'observation: ", bold: true }),
                                            new TextRun(formState.formData.dateObservation || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Date de notification: ", bold: true }),
                                            new TextRun(formState.formData.dateNotification || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Canal primaire: ", bold: true }),
                                            new TextRun(getCanalLabel(formState.formData.canalPrimaire) || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),

                                    // Afficher le canal précisé si "autre" est sélectionné
                                    ...(formState.formData.canalPrimaire === "autre"
                                        ? [
                                            new Paragraph({
                                                children: [
                                                    new TextRun({ text: "Précision canal: ", bold: true }),
                                                    new TextRun(formState.formData.autreCanalPrimaire || "Non précisé"),
                                                ],
                                                spacing: { after: 100 },
                                            }),
                                        ]
                                        : []),

                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Liens: ", bold: true }),
                                            new TextRun(formState.formData.liens || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Auteur: ", bold: true }),
                                            new TextRun(formState.formData.auteur || "Non renseigné"),
                                        ],
                                        spacing: { after: 400 },
                                    }),

                                    // Section Analyse
                                    new Paragraph({
                                        text: "Analyse de l'Alerte",
                                        heading: HeadingLevel.HEADING_2,
                                        spacing: {
                                            before: 400,
                                            after: 200,
                                        },
                                        border: {
                                            bottom: {
                                                color: "auto",
                                                space: 1,
                                                style: BorderStyle.SINGLE,
                                                size: 6,
                                            },
                                        },
                                    }),

                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Objet principal: ", bold: true }),
                                            new TextRun(formState.formData.objetPrincipal || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Propos: ", bold: true }),
                                            new TextRun(formState.formData.propos || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Niveau d'alerte: ", bold: true }),
                                            new TextRun(getNiveauLabel(formState.formData.niveauAlerte) || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Impact potentiel: ", bold: true }),
                                            new TextRun(
                                                formState.formData.impactPotentiel.length > 0
                                                    ? formState.formData.impactPotentiel.map((impact) => getImpactLabel(impact)).join(", ")
                                                    : "Non renseigné",
                                            ),
                                        ],
                                        spacing: { after: 100 },
                                    }),

                                    // Afficher l'autre impact si sélectionné
                                    ...(formState.formData.impactPotentiel.includes("autre")
                                        ? [
                                            new Paragraph({
                                                children: [
                                                    new TextRun({ text: "Autre impact: ", bold: true }),
                                                    new TextRun(formState.formData.autreImpact || "Non précisé"),
                                                ],
                                                spacing: { after: 100 },
                                            }),
                                        ]
                                        : []),

                                    // Section Gestion
                                    new Paragraph({
                                        text: "Gestion de l'Alerte",
                                        heading: HeadingLevel.HEADING_2,
                                        spacing: {
                                            before: 400,
                                            after: 200,
                                        },
                                        border: {
                                            bottom: {
                                                color: "auto",
                                                space: 1,
                                                style: BorderStyle.SINGLE,
                                                size: 6,
                                            },
                                        },
                                    }),

                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Actions immédiates: ", bold: true }),
                                            new TextRun(formState.formData.actionsImmédiates || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Propositions: ", bold: true }),
                                            new TextRun(formState.formData.propositions || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Responsable: ", bold: true }),
                                            new TextRun(formState.formData.responsable || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                    new Paragraph({
                                        children: [
                                            new TextRun({ text: "Notes: ", bold: true }),
                                            new TextRun(formState.formData.notes || "Non renseigné"),
                                        ],
                                        spacing: { after: 100 },
                                    }),
                                ],
                            },
                        ],
                    })

                    // Générer le document Word
                    const blob = await Packer.toBlob(doc)

                    // Créer un lien de téléchargement
                    const url = URL.createObjectURL(blob)
                    const a = document.createElement("a")
                    const today = new Date()
                    const fileName = `Fiche_Alerte_DTD_${today.getFullYear()}${(today.getMonth() + 1).toString().padStart(2, "0")}${today.getDate().toString().padStart(2, "0")}.docx`

                    a.href = url
                    a.download = fileName
                    document.body.appendChild(a)
                    a.click()

                    // Nettoyer
                    window.URL.revokeObjectURL(url)
                    document.body.removeChild(a)
                } catch (error) {
                    console.error("Erreur lors de la génération du document Word:", error)
                    alert("Une erreur est survenue lors de la génération du document Word. Veuillez réessayer.")
                } finally {
                    // Réinitialiser le bouton
                    downloadWordButton.disabled = false
                    downloadWordButton.innerHTML = '<i data-lucide="file-text" class="w-4 h-4">📝</i> Télécharger en Word'
                }
            }

            // Initialize UI
            updateUI()
        })
