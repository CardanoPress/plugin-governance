window.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine || {}
    const cardanoPress = window.cardanoPress || {}

    const percentage = (partial, total) => {
        return (100 * partial) / total
    }

    const sumArray = array => {
        return Object.values(array).reduce((a, b) => a + b)
    }

    Alpine.data('cardanoPressGovernance', () => ({
        isProcessing: false,
        options: [],
        selected: '',
        voted: '',

        async init() {
            this.voted = this.$root.dataset.voted
            this.selected = this.voted
            this.options = JSON.parse(this.$root.dataset.options)

            console.log('CardanoPress Governance ready!')
        },

        getData(option, inPercentage = false) {
            const value = this.options[option]

            return inPercentage ? percentage(value, sumArray(this.options)).toFixed(2) : value
        },

        isDisabled(isSubmit = false) {
            return !this.isConnected || this.isProcessing || (isSubmit ? !!!this.selected : false) || !!this.voted
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
