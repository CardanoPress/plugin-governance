import { cardanoPressGovernanceMessages, handleVote } from './actions'

window.addEventListener('alpine:init', () => {
    const percentage = (partial: number, total: number) => {
        return ((100 * partial) / total) || 0
    }

    const sumArray = (array: Record<string, string | any>) => {
        return Object.values(array).reduce((a, b) => a + b)
    }

    window.Alpine.data('cardanoPressGovernance', () => ({
        isProcessing: false,
        options: [] as unknown as Record<string, string>,
        selected: '',
        voted: '',
        power: 0,
        winner: '',

        async init() {
            this.voted = this.$root.dataset.voted || ''
            this.power = parseInt(this.$root.dataset.power || '')
            this.selected = this.voted
            this.options = JSON.parse(this.$root.dataset.options || '[]')

            if (this.$root.dataset.complete && 0 !== Object.values(this.options).filter(v => parseInt(v) > 0).length) {
                this.winner = Object.keys(this.options).reduce((a, b) => (this.options[a] > this.options[b]) ? a : b)
                this.selected = this.winner
            }

            console.log('CardanoPress Governance ready!')
        },

        getData(option: string, inPercentage = false) {
            const value = parseInt(this.options[option])

            return inPercentage ? percentage(value, sumArray(this.options)).toFixed(2) : value
        },

        isDisabled(isSubmit = false) {
            return (
                // @ts-ignore
                !this.isConnected ||
                !this.power ||
                this.isProcessing ||
                (isSubmit ? !!!this.selected : false) ||
                !!this.voted
            )
        },

        isWinner(option: string) {
            return option === this.winner
        },

        hasVoted(option: string) {
            return this.voted === option
        },

        async handleVote() {
            window.cardanoPress.api.addNotice({
                id: 'proposalVote',
                type: 'info',
                text: cardanoPressGovernanceMessages.voting,
            })

            this.isProcessing = true
            const proposalId = this.$root.dataset.proposal || '0'
            const response = await handleVote(proposalId, this.selected)

            window.cardanoPress.api.removeNotice('proposalVote')

            if (response.success) {
                this.options = response.data.data
                this.voted = this.selected

                window.cardanoPress.api.addNotice({ type: 'info', text: response.data.message })
            } else {
                window.cardanoPress.api.addNotice({ type: 'warning', text: response.data })
            }

            this.isProcessing = false
        },
    }))
})
