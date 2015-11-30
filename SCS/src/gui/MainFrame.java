package gui;

import javax.swing.*;
import java.awt.*;

public class MainFrame extends JFrame {

    private Dimension screenSize;
    private static final String windowTitle = "EazyBanking SCS Tan generator";

    public MainFrame() {
        super.setTitle(windowTitle);
        super.setSize(800, 800);
        screenSize = Toolkit.getDefaultToolkit().getScreenSize();
        super.setLocation((int) (screenSize.getWidth()*0.1), (int) (screenSize.getHeight()*0.1));
        super.setDefaultCloseOperation(WindowConstants.EXIT_ON_CLOSE);
    }

    public void makeVisible() {
        super.setVisible(true);
    }
}
