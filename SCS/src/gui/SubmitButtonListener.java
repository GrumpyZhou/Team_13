package gui;

import TanLogic.TanCalculator;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.geom.Arc2D;

public class SubmitButtonListener implements ActionListener {

    private String transferAmountInput;
    private String targetAccountInput;
    private String pinInput;
    private MainFrame mainFrame;
    private InputSanityChecker sanityChecker;

    public SubmitButtonListener(MainFrame mainFrame) {
        this.mainFrame = mainFrame;
        sanityChecker = new InputSanityChecker();
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        transferAmountInput = mainFrame.inputBoxes[0].getText();
        targetAccountInput = mainFrame.inputBoxes[1].getText();
        pinInput = mainFrame.inputBoxes[2].getText();
        if(!checkInputSanity()) {
            return;
        }
        TanCalculator tanCalculator = new TanCalculator();
        mainFrame.displayTAN(tanCalculator.getTan(pinInput, Double.parseDouble(transferAmountInput), targetAccountInput));
    }

    private boolean checkInputSanity() {
        if(!sanityChecker.checkTransferAmountInput(transferAmountInput)) {
            mainFrame.displayErrorMessage("Invalid transfer amount!");
            return false;
        }
        if(!sanityChecker.checkTargetAccountInput(targetAccountInput)) {
            mainFrame.displayErrorMessage("Invalid target account!");
            return false;
        }
        if(!sanityChecker.checkPinInput(pinInput)) {
            mainFrame.displayErrorMessage("Invalid PIN!");
            return false;
        }
        return true;
    }
}
