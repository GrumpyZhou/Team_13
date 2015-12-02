package gui;

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
            int i = 1;
            while(line != null) {
                if(!checkBatchLine(line)) {
                    mainFrame.displayErrorMessage("Error in batch file line " + i);
                    return;
                }
                lines.add(line);
                i++;
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

    private void showTan(LinkedList<String> lines) {
        int count = lines.size();
        double[] amounts = new double[count];
        String[] targetAccounts = new String[count];
        //TODO
    }

    private boolean checkBatchLine(String line) {
        //TODO
        return false;
    }
}
