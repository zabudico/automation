pipeline {
    agent { label 'ansible-agent' }

    stages {
        stage('Copy project to ansible-agent') {
            steps {
                sh 'rm -rf /home/jenkins/php_arrays'          // очищаем старое
                sh 'cp -r ../php_arrays /home/jenkins/'       // копируем актуальный проект
            }
        }
        stage('Run Ansible playbook') {
            steps {
                sh '''
                ansible-playbook \
                  -i ansible/hosts.ini \
                  ansible/setup_test_server.yml \
                  --ssh-common-args="-o StrictHostKeyChecking=no"
                '''
            }
        }
    }

    post {
        success {
            echo "Деплой успешен! Открывай http://localhost:8081"
        }
    }
}