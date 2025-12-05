pipeline {
    agent { label 'php-agent' }
    stages {
        stage('Checkout') {
            steps { git 'https://github.com/zabudico/automation.git' }
        }
        stage('Install Composer') {
            steps { sh 'composer install --no-dev' }
        }
        stage('Run Tests') {
            steps { sh 'php simple_tests.php' }
        }
    }
}