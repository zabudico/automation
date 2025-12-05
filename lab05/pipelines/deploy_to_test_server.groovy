pipeline {
    agent { label 'ansible-agent' }
    stages {
        stage('Deploy PHP project') {
            steps {
                sh '''
                rsync -av --delete php_project/ test-server:/var/www/myphpproject/
                '''
            }
        }
    }
}