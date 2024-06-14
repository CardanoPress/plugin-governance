export const cardanoPressGovernanceMessages = window.cardanoPressGovernanceMessages || {
    voting: '',
    invalid: '',
}

const transformToAda = (proposalId, optionValue) => {
    proposalId = parseFloat(proposalId) * 100
    optionValue = parseFloat(optionValue)

    // ADA Amount = 1.xxxxyy
    // xxxx = proposalId
    // yy   = optionValue
    return (1 + (proposalId + optionValue) / 1000000).toFixed(6)
}

export const handleVote = async (proposalId, optionValue) => {
    if ('0' === proposalId) {
        return {
            success: false,
            data: cardanoPressGovernanceMessages.invalid,
        }
    }

    const verification = await verifyVote(proposalId, optionValue)

    if (!verification.success) {
        return verification
    }

    const result = await pushTransaction(proposalId, optionValue, verification.data)

    if (result.success) {
        return await pushToDB(proposalId, optionValue, result.data.transaction)
    }

    return result
}

const verifyVote = async (proposalId, optionValue) => {
    return await fetch(cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: cardanoPress._nonce,
            action: 'cp-governance_proposal_vote_verify',
            proposalId,
            optionValue,
        }),
    }).then((response) => response.json())
}

const pushTransaction = async (proposalId, optionValue, votingData) => {
    const adaAmount = transformToAda(proposalId, optionValue)

    try {
        const amount = cardanoPress.api.adaToLovelace(adaAmount)
        const Wallet = await cardanoPress.api.getConnectedWallet()
        const address = await Wallet.getChangeAddress()

        votingData.votingFee.amount = cardanoPress.api.adaToLovelace(votingData.votingFee.amount)

        return await cardanoPress.wallet.multisendTx(
            [{ address, amount }, votingData.votingFee].filter((output) => output.amount && output.address)
        )
    } catch (error) {
        return {
            success: false,
            data: error,
        }
    }
}

const pushToDB = async (proposalId, optionValue, transaction) => {
    return await fetch(cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: cardanoPress._nonce,
            action: 'cp-governance_proposal_vote_complete',
            proposalId,
            optionValue,
            transaction,
        }),
    }).then((response) => response.json())
}
