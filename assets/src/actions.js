export const handleVote = async (proposalId, optionValue) => {
    if ('0' === proposalId) {
        return {
            success: false,
            data: 'Invalid proposal ID',
        }
    }

    const result = await pushTransaction(proposalId, optionValue)

    if (result.success) {
        return await pushToDB(proposalId, optionValue, result.transaction)
    }

    return result
}

const pushTransaction = async (proposalId, optionValue) => {
    const adaAmount = 1 + (((proposalId * 1000) + (optionValue * 1)) / 1000000)
    // ADA Amount = 1.xxxyyy
    // xxx = proposalId
    // yyy = optionValue

    try {
        const amount = cardanoPress.api.adaToLovelace(adaAmount)
        const Wallet = await cardanoPress.api.getConnectedWallet()
        const address = await Wallet.getChangeAddress()

        return await cardanoPress.wallet.paymentTx(address, amount)
    } catch (error) {
        return {
            success: false,
            data: error,
        }
    }
}

const pushToDB = async (proposalId, option, transaction) => {
    return await fetch(cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: cardanoPress._nonce,
            action: 'cp-governance_proposal_vote',
            proposalId,
            option,
            transaction,
        }),
    }).then((response) => response.json())
}
