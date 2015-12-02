package gui;

public class InputSanityChecker {

    public InputSanityChecker() {}

    public boolean checkPinInput(String input) {
        if(input.length() != 6)
            return false;
        for(char c : input.toCharArray()) {
            if(c < '0' || c > '9')
                return false;
        }
        return true;
    }

    public boolean checkTargetAccountInput(String input) {
        if(input.length() > 10)
            return false;
        for(char c : input.toCharArray()) {
            if(c < '0' || c > '9')
                return false;
        }
        return true;
    }

    public boolean checkTransferAmountInput(String input) {
        double amount;
        try {
            amount = Double.parseDouble(input);
            if(amount <= 0)
                return false;
            return true;
        } catch (Exception e) {
            return false;
        }
    }
}
