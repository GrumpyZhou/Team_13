package TanLogic;

public class TanCalculator {

    public TanCalculator() {}

    /**
     * Function to use for single transactions.
     * @param pin The users pin number.
     * @param transferAmount Amount to transfer to target account.
     * @param targetAccount Target account number.
     * @return Returns the generated tan as a String.
     */
    public String getTan(String pin, double transferAmount, String targetAccount) {
        //TODO
        return "";
    }

    /**
     * Function to use for transaction batch files.
     * @param pin The users pin number.
     * @param transferAmount Amount to transfer to target account for corresponding transaction (Array).
     * @param targetAccount Array of target account numbers for every transaction.
     * @return Returns the generated tan as a String.
     */
    public String getTan(String pin, double[] transferAmount, String[] targetAccount) {
        //TODO
        return "";
    }
}
