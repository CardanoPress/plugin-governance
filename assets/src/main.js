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
        winner: '',

        async init() {
            this.voted = this.$root.dataset.voted
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
            return !this.isConnected || this.isProcessing || (isSubmit ? !!!this.selected : false) || !!this.voted
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
                text: 'Processing...',
            })

            this.isProcessing = true
            const response = await this.pushToDB(this.selected)

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

        async pushToDB(option) {
            const proposalId = this.$root.id.replace('proposal-', '') || 0

            return await fetch(cardanoPress.ajaxUrl, {
                method: 'POST',
                body: new URLSearchParams({
                    _wpnonce: cardanoPress._nonce,
                    action: 'cp-governance_proposal_vote',
                    proposalId,
                    option,
                }),
            }).then((response) => response.json())
        },
    }))
})
