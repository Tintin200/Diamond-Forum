import { Controller } from '@hotwired/stimulus';

/*
 * Contrôleur Stimulus pour le formulaire de connexion Diamond Forum.
 * Remplace le useState(showPassword) de la maquette v0.app.
 *
 * Utilisation dans Twig :
 *   <div data-controller="login-form">
 *     <input data-login-form-target="password" type="password">
 *     <button data-action="login-form#toggleVisibility" data-login-form-target="toggleIcon">
 */
export default class extends Controller {
    static targets = ['password', 'toggleIcon'];

    toggleVisibility() {
        if (!this.hasPasswordTarget) return;

        const isHidden = this.passwordTarget.type === 'password';
        this.passwordTarget.type = isHidden ? 'text' : 'password';

        if (this.hasToggleIconTarget) {
            this.toggleIconTarget.innerHTML = isHidden
                ? this.eyeOffIcon()
                : this.eyeIcon();
            this.toggleIconTarget.closest('button')?.setAttribute(
                'aria-label',
                isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe'
            );
        }
    }

    eyeIcon() {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>';
    }

    eyeOffIcon() {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M6.61 6.61C3.94 8.44 2 12 2 12s4 8 11 8a9.3 9.3 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';
    }
}
