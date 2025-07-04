resource "aws_instance" "docker_web" {
  ami           = "ami-05ffe3c48a9991133" # Amazon Linux 2 (adjust if needed)
  instance_type = "t2.micro"
  subnet_id     = aws_subnet.public_subnet_1.id
  key_name      = var.vockey
  security_groups = [aws_security_group.web_sg.id]

  user_data = <<-EOF
              #!/bin/bash
              yum update -y
              yum install -y docker git
              service docker start
              systemctl enable docker
              usermod -aG docker ec2-user
              cd /home/ec2-user
              git clone https://github.com/LamYuetXin/cloudsec.git
              cd cloudsec
              docker build -t assignment-app .
              docker run -d -p 80:80 --name assignment assignment-app
              EOF

  tags = {
    Name = "DockerBakeryApp"
  }
}
