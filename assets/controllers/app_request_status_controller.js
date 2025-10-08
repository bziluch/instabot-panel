import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static values = {
        id: Number,
        url: String,
    }

    connect() {
        console.log(`[Stimulus] Start polling request #${this.idValue}`)
        this.pollInterval = setInterval(() => this.checkStatus(), 5000) // co 5s
        this.twofaForm = document.getElementById('twofa-form')
        this.checkStatus() // natychmiast pierwsze sprawdzenie
    }

    disconnect() {
        clearInterval(this.pollInterval)
    }

    async checkStatus() {
        try {
            const response = await fetch(this.urlValue)
            if (!response.ok) throw new Error("Request failed")

            const data = await response.json()
            const statusBox = document.getElementById("status-box")

            if (data.status === 0) {
                statusBox.className = "alert alert-secondary"
                statusBox.innerText = "Oczekiwanie na odpowiedź z aplikacji..."
            } else if (data.status === 1) {
                let res = {}
                try { res = JSON.parse(data.response) } catch (e) {}

                if (res.success === true) {
                    statusBox.className = "alert alert-success"
                    statusBox.innerText = "✅ Logowanie zakończone sukcesem!"
                } else if (res.need_2fa === true) {
                    statusBox.className = "alert alert-warning"
                    statusBox.innerText = "⚠️ Wymagany kod 2FA!"
                    this.twofaForm.classList.remove('d-none');
                } else {
                    statusBox.className = "alert alert-danger"
                    statusBox.innerText = "❌ Nie udało się zalogować!"
                }

                clearInterval(this.pollInterval)
            } else if (data.status === 3) {
                statusBox.className = "alert alert-danger"
                statusBox.innerText = "❌ Żądanie anulowane!"
                clearInterval(this.pollInterval)
            }

        } catch (err) {
            console.error(err)
            this.recoverOnError()
        }
    }

    recoverOnError() {
        // fallback: jeśli coś się zepsuje, odśwież stronę po 15 sekundach
        setTimeout(() => {
            console.warn("[Stimulus] AJAX error, refreshing fallback...")
            window.location.reload()
        }, 15000)
    }
}
