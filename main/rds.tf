
# Ensure your VPC and Subnets are created first (use your VPC and private subnets)
resource "aws_db_instance" "my_db" {
  allocated_storage    = 20  # Storage size in GB
  db_name             = "bakerydb"  # Name of the initial database
  engine              = "mysql"  # Database engine
  engine_version      = "8.0"  # MySQL version
  instance_class      = "db.t3.micro"  # Use t2.micro or other allowed instance sizes
  username            = "admin"  # Master username for the DB
  password            = "passw0rd1234"  # Master password (Change this)
  db_subnet_group_name = aws_db_subnet_group.main.id  # Subnet group for RDS
  multi_az            = false  # Multi-AZ is not allowed in the sandbox
  publicly_accessible = false  # Do not expose publicly
  storage_encrypted   = true  # Enable encryption at rest

  # Backup and retention settings
  backup_retention_period = 7  # Retain backups for 7 days

  # VPC security groups
  vpc_security_group_ids = [aws_security_group.db_sg.id]

  # Tags
  tags = {
    Name = "MyMySQLDatabase"
  }

  # Final DB creation options
  skip_final_snapshot = true  # Skip snapshot when deleting the DB (optional)
}


# Output RDS endpoint
output "rds_endpoint" {
  value = aws_db_instance.my_db.endpoint
}

# Output RDS DB instance ID
output "rds_instance_id" {
  value = aws_db_instance.my_db.id
}
