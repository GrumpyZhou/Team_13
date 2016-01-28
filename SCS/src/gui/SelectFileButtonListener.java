package gui;

import TanLogic.TanCalculator;

import javax.swing.*;
import javax.swing.filechooser.FileNameExtensionFilter;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.*;
import java.util.LinkedList;

public class SelectFileButtonListener implements ActionListener {

    private MainFrame mainFrame;
    private String pinInput;

    public SelectFileButtonListener(MainFrame mainFrame) {
        this.mainFrame = mainFrame;
    }

    @Override
    public void actionPerformed(ActionEvent e) {
        if(!checkPinInput())
            return;
        JFileChooser fileChooser = new JFileChooser();
        fileChooser.setFileFilter(new FileNameExtensionFilter("TEXT FILES", "txt", "text"));
        int rc = fileChooser.showDialog(mainFrame, "Select");
        if(rc != JFileChooser.APPROVE_OPTION)
            return;
        File batchFile = fileChooser.getSelectedFile();
        processBatchFile(batchFile);
    }

    private void processBatchFile(File batchFile) {
        BufferedReader reader;
        try {
            reader = new BufferedReader(new FileReader(batchFile));
            LinkedList<String> lines = new LinkedList<String>();
            String line = reader.readLine();
            while(line != null) {
                lines.add(line);
                line = reader.readLine();
            }
            showTan(lines);
        } catch (IOException e) {
            mainFrame.displayErrorMessage("Batch file not readable.");
            return;
        }
    }

    private boolean checkPinInput() {
        pinInput = mainFrame.inputBoxes[2].getText();
        InputSanityChecker sanityChecker = new InputSanityChecker();
        if(!sanityChecker.checkPinInput(pinInput)) {
            mainFrame.displayErrorMessage("Invalid PIN!");
            return false;
        }
        return true;
    }

    private boolean showTan(LinkedList<String> lines) {
        int count = lines.size();
        double[] amounts = new double[count];
        String[] targetAccounts = new String[count];
        for(int i = 0; i < count; i++) {
            String str = lines.get(i);
            str = str.trim();
            String[] vals = str.split(" ", -1);
            for(int j = 0; j < 2; j++) {
                vals[j] = vals[j].substring(1, vals[j].length()-1);
            }
            if(str.length() > 150 || !checkBatchLine(vals[0], vals[1])) {
                mainFrame.displayErrorMessage("Error in batch file line " + (i+1));
                return false;
            }
            targetAccounts[i] = vals[0];
            amounts[i] = Double.parseDouble(vals[1]);
        }
        TanCalculator tanCalculator = new TanCalculator();
        String tan = tanCalculator.getTan(pinInput, amounts, targetAccounts);
        mainFrame.displayTAN(tan);
        return true;
    }

    private boolean checkBatchLine(String accountNumber, String amount) {
        InputSanityChecker inputSanityChecker = new InputSanityChecker();
        if(!inputSanityChecker.checkTargetAccountInput(accountNumber) || !inputSanityChecker.checkTransferAmountInput(amount))
            return false;
        return true;
    }
}
