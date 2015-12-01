package gui;

import javax.swing.*;
import java.awt.*;

public class MainFrame extends JFrame {

    private Dimension screenSize;
    private JTextField outputBox;
    private static final String windowTitle = "EazyBanking SCS Tan generator";

    public MainFrame() {
        super.setTitle(windowTitle);
        super.setSize(400, 170);
        screenSize = Toolkit.getDefaultToolkit().getScreenSize();
        super.setLocation((int) (screenSize.getWidth()*0.1), (int) (screenSize.getHeight()*0.1));
        super.setDefaultCloseOperation(WindowConstants.EXIT_ON_CLOSE);
        super.setLayout(new GridBagLayout());
        outputBox = new JTextField("TAN Generator");
        outputBox.setEditable(false);
        outputBox.setBackground(Color.WHITE);
        outputBox.setHorizontalAlignment(JTextField.CENTER);
        populateFrame();
    }

    private void populateFrame() {
        String[] labels = {"Amount: ", "Target Account: ", "Pin: "};
        GridBagConstraints c = new GridBagConstraints();
        for(int i = 0; i < labels.length; i++) {
            c.fill = GridBagConstraints.NONE;
            c.weightx = 0.2;
            c.gridx = 0;
            c.gridy = i;
            c.anchor = GridBagConstraints.LINE_START;
            super.add(new JLabel(labels[i]), c);
            c = new GridBagConstraints();
            c.fill = GridBagConstraints.HORIZONTAL;
            c.weightx = 0.8;
            c.gridx = 1;
            c.gridy = i;
            super.add(new JTextField(), c);
        }
        String[] buttonTexts = {"Submit", "Use batch file"};
        for(int i = 0; i < 2; i++) {
            c = new GridBagConstraints();
            c.gridx = i;
            c.gridy = labels.length;
            c.weightx = 0.5;
            c.ipadx = 10;
            c.insets = new Insets(10, 0, 10, 0);
            c.anchor = GridBagConstraints.PAGE_END;
            super.add(new JButton(buttonTexts[i]), c);
        }
        c = new GridBagConstraints();
        c.gridx = 0;
        c.gridy = labels.length+1;
        c.gridwidth = 2;
        c.insets = new Insets(0, 20, 0, 20);
        c.fill = GridBagConstraints.HORIZONTAL;
        super.add(outputBox, c);
    }

    public void makeVisible() {
        super.setVisible(true);
    }
}
