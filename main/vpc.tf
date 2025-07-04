# Create VPC
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  instance_tenancy     = "default"
  enable_dns_support   = true
  enable_dns_hostnames = true

  tags = {
    Name = "bakery-vpc"
  }
}



# Create Public Subnet 1
resource "aws_subnet" "public_subnet_1" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.1.0/24"
  availability_zone       = "us-east-1a"
  map_public_ip_on_launch = true

  tags = {
    Name = "main-subnet-public1-us-east-1a"
  }
}

# Create Public Subnet 2
resource "aws_subnet" "public_subnet_2" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.2.0/24"
  availability_zone       = "us-east-1b"
  map_public_ip_on_launch = true

  tags = {
    Name = "main-subnet-public2-us-east-1b"
  }
}

# Create Private Subnet 1
resource "aws_subnet" "private_subnet_1" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.3.0/24"
  availability_zone       = "us-east-1a"

  tags = {
    Name = "main-subnet-private1-us-east-1a"
  }
}

# Create Private Subnet 2
resource "aws_subnet" "private_subnet_2" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.4.0/24"
  availability_zone       = "us-east-1b"

  tags = {
    Name = "main-subnet-private2-us-east-1b"
  }
}

# Create Internet Gateway
resource "aws_internet_gateway" "main_igw" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = "main-igw"
  }
}

# Create Route Table for Public Subnets
resource "aws_route_table" "public_rtb" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.main_igw.id
  }

  tags = {
    Name = "main-rtb-public"
  }
}

# Associate Public Subnet 1 with Public Route Table
resource "aws_route_table_association" "public_rtb_association_1" {
  subnet_id      = aws_subnet.public_subnet_1.id
  route_table_id = aws_route_table.public_rtb.id
}

# Associate Public Subnet 2 with Public Route Table
resource "aws_route_table_association" "public_rtb_association_2" {
  subnet_id      = aws_subnet.public_subnet_2.id
  route_table_id = aws_route_table.public_rtb.id
}

# Create Route Table for Private Subnet 1
resource "aws_route_table" "private_rtb_1" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = "main-rtb-private1-us-east-1a"
  }
}

# Associate Private Subnet 1 with Private Route Table
resource "aws_route_table_association" "private_rtb_association_1" {
  subnet_id      = aws_subnet.private_subnet_1.id
  route_table_id = aws_route_table.private_rtb_1.id
}

# Create Route Table for Private Subnet 2
resource "aws_route_table" "private_rtb_2" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = "main-rtb-private2-us-east-1b"
  }
}

# Associate Private Subnet 2 with Private Route Table
resource "aws_route_table_association" "private_rtb_association_2" {
  subnet_id      = aws_subnet.private_subnet_2.id
  route_table_id = aws_route_table.private_rtb_2.id
}

# Create VPC Endpoint for S3
resource "aws_vpc_endpoint" "main_vpce_s3" {
  vpc_id       = aws_vpc.main.id
  service_name = "com.amazonaws.us-east-1.s3"
  route_table_ids = [
    aws_route_table.private_rtb_1.id,
    aws_route_table.private_rtb_2.id
  ]

  tags = {
    Name = "main-vpce-s3"
  }
}

# Create a DB Subnet Group (Private Subnets)
resource "aws_db_subnet_group" "main" {
  name        = "bakery-db-subnet-group"
  subnet_ids  = [aws_subnet.private_subnet_1.id, aws_subnet.private_subnet_2.id]  # Use your private subnets
  description = "Bakery database subnet group"

  tags = {
    Name = "MyDbSubnetGroup"
  }
}

# Output the VPC ID and Subnets
output "vpc_id" {
  value = aws_vpc.main.id
}

output "public_subnet_1_id" {
  value = aws_subnet.public_subnet_1.id
}

output "public_subnet_2_id" {
  value = aws_subnet.public_subnet_2.id
}

output "private_subnet_1_id" {
  value = aws_subnet.private_subnet_1.id
}

output "private_subnet_2_id" {
  value = aws_subnet.private_subnet_2.id
}

output "vpc_endpoint_id" {
  value = aws_vpc_endpoint.main_vpce_s3.id
}
