import type ReCaptchaV2 from '@types/grecaptcha'
import type { Alpine as AlpineType } from 'alpinejs'

declare global {
    interface Window {
        Alpine: AlpineType
        cardanoPress: Record<string, string | any>
        cardanoPressGovernanceMessages: Record<string, string>
        cardanoPressRecaptchaCallback: () => void
        grecaptcha: ReCaptchaV2.ReCaptcha
    }
}

export { }

