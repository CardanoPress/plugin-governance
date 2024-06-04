import { cardanoPressGovernanceMessages, handleVote } from './actions'

window.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine || {}
    const cardanoPress = window.cardanoPress || {}

    const percentage = (partial, total) => {
        return ((100 * partial) / total) || 0
    }

    const sumArray = array => {
        return Object.values(array).reduce((a, b) => a + b)
    }

    Alpine.data('cardanoPressGovernance', () => ({
        isProcessing: false,
        options: [],
        selected: '',
        voted: '',
        power: '',
        winner: '',

        async init() {
            this.voted = this.$root.dataset.voted
            this.power = parseInt(this.$root.dataset.power)
            this.selected = this.voted
            this.options = JSON.parse(this.$root.dataset.options)

            if (this.$root.dataset.complete && 0 !== Object.values(this.options).filter(v => v > 0).length) {
                this.winner = Object.keys(this.options).reduce((a, b) => (this.options[a] > this.options[b]) ? a : b)
                this.selected = this.winner
            }

            console.log('CardanoPress Governance ready!')
        },

        getData(option, inPercentage = false) {
            const value = this.options[option]

            return inPercentage ? percentage(value, sumArray(this.options)).toFixed(2) : value
        },

        isDisabled(isSubmit = false) {
            return (
                !this.isConnected ||
                !this.power ||
                this.isProcessing ||
                (isSubmit ? !!!this.selected : false) ||
                !!this.voted
            )
        },

        isWinner(option) {
            return option === this.winner
        },

        hasVoted(option) {
            return this.voted === option
        },

        async handleVote() {
            cardanoPress.api.addNotice({
                id: 'proposalVote',
                type: 'info',
                text: cardanoPressGovernanceMessages.voting,
            })

            this.isProcessing = true
            const proposalId = this.$root.dataset.proposal || '0'
            const response = await handleVote(proposalId, this.selected)

            cardanoPress.api.removeNotice('proposalVote')

            if (response.success) {
                this.options = response.data.data
                this.voted = this.selected

                cardanoPress.api.addNotice({ type: 'info', text: response.data.message })
            } else {
                cardanoPress.api.addNotice({ type: 'warning', text: response.data })
            }

            this.isProcessing = false
        },
    }))
})
